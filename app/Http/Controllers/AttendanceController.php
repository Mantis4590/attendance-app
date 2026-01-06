<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\StampCorrectionRequest;

class AttendanceController extends Controller
{
    public function index()
    {
        $now = now();

        $weekMap = [
            'Mon' => '月',
            'Tue' => '火',
            'Wed' => '水',
            'Thu' => '木',
            'Fri' => '金',
            'Sat' => '土',
            'Sun' => '日',
        ];

        $weekJp = $weekMap[$now->format('D')];
        $nowDate = $now->format("Y年n月j日({$weekJp})");
        $nowTime = $now->format('H:i');

        $attendance = Attendance::with('breakTimes')
            ->where('user_id', auth()->id())
            ->where('date', $now->toDateString())
            ->first();

        // デフォルト
        $status = '勤務外';

        if ($attendance) {

            // ① 休憩中判定（最優先）
            $latestBreak = $attendance->breakTimes
                ->sortByDesc('break_start')
                ->first();

            if ($latestBreak && is_null($latestBreak->break_end)) {
                $status = '休憩中';

            // ② 退勤済
            } elseif (!is_null($attendance->clock_out)) {
                $status = '退勤済';

            // ③ 出勤中
            } elseif (!is_null($attendance->clock_in)) {
                $status = '出勤中';
            }
        }

        return view('attendance.index', compact(
            'nowDate',
            'nowTime',
            'status',
            'attendance'
        ));
    }

    public function clockIn() {
        Attendance::create([
            'user_id' => auth()->id(),
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'status' => '出勤中',
        ]);

        return back();
    }

    public function startBreak()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->firstOrFail();

        // 休憩開始（条件は clock_in があることだけで十分）
        $attendance->breakTimes()->create([
            'break_start' => now(),
            'break_end'   => null,
        ]);

        // 表示用ステータス
        $attendance->update([
            'status' => '休憩中',
        ]);

        return back();
    }

    public function endBreak() {
        $attendance = Attendance::where('user_id', auth()->id())
        ->where('date', today())
        ->firstOrFail();

        // 終了していない最新の休憩を取得
        $break = $attendance->breakTimes()
            ->whereNull('break_end')
            ->latest('break_start')
            ->first();

        if ($break) {
            // 今の休憩を終了
            $break->update([
                'break_end' => now(),
            ]);

            // その日の休憩レコードを全部ロード
            $attendance->load('breakTimes');

            // 休憩合計を「分」で再計算
            $totalMinutes = $attendance->breakTimes
                ->filter(function ($breakRecord) {
                    return $breakRecord->break_start && $breakRecord->break_end;
                })
                ->sum(function ($breakRecord) {
                    return $breakRecord->break_start->diffInMinutes($breakRecord->break_end);
                });

            // 分 -> HH:MM に変換
            $hours = intdiv($totalMinutes, 60);
            $minutes = $totalMinutes % 60;

            $attendance->update([
                'total_break' => sprintf('%02d:%02d', $hours, $minutes),
                'status' => '出勤中',
            ]);
        }

        return back();
    }

    public function clockOut() {
        $attendance = Attendance::where('user_id', auth()->id())
        ->where('date', now()->toDateString())
        ->first();

        if ($attendance) {

            // 退勤時刻
            $clockOut = now();

            // 出勤時刻
            $clockIn = Carbon::parse($attendance->clock_in);

            // 休憩合計（分）
            $totalBreakMinutes = 0;
            if ($attendance->total_break) {
                $parts = explode(':', $attendance->total_break);
                $totalBreakMinutes = ($parts[0] * 60) + $parts[1];
            }

            // 勤務時間（分）
            $workMinutes = $clockIn->diffInMinutes($clockOut) - $totalBreakMinutes;

            if ($workMinutes < 0) {
                $workMinutes = 0;
            }

            // 分->時:分　変換
            $hours = floor($workMinutes / 60);
            $minutes = $workMinutes % 60;

            // 保存
            $attendance->update([
                'clock_out' => $clockOut,
                'total_work' => sprintf('%02d:%02d', $hours, $minutes),
                'status' => '退勤済み',
            ]);
        }

        return back();
    }


    public function list(Request $request)
    {
        $targetMonth = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')
            : Carbon::now()->startOfMonth();

        $prevMonth = $targetMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $targetMonth->copy()->addMonth()->format('Y-m');

        // その月の勤怠を取得（date をキーにする）
        $attendances = Attendance::where('user_id', auth()->id())
            ->whereBetween('date', [
                $targetMonth->copy()->startOfMonth(),
                $targetMonth->copy()->endOfMonth(),
            ])
            ->get()
            ->keyBy(fn ($a) => $a->date->format('Y-m-d'));

        // 月の日付一覧を作る
        $dates = [];
        $day = $targetMonth->copy()->startOfMonth();
        $end = $targetMonth->copy()->endOfMonth();

        while ($day <= $end) {
            $dates[] = $day->copy();
            $day->addDay();
        }

        return view('attendance.list', compact(
            'dates',
            'attendances',
            'targetMonth',
            'prevMonth',
            'nextMonth'
        ));
    }


    public function detail($id) {
        $attendance = Attendance::with('breakTimes')
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        // ユーザー情報（名前表示のため）
        $user = auth()->user();

        // この勤怠に対する承認待ち申請があるか
        $pendingRequest = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->first();

        $hasPendingRequest = $pendingRequest !== null;

        $hasApprovedRequest = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'approved')
            ->exists();

        return view('attendance.detail',compact('attendance', 'user', 'hasPendingRequest',
        'hasApprovedRequest',
        'pendingRequest'
        ));
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        StampCorrectionRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => $attendance->id,
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'breaks' => $request->breaks,
            'note' => $request->note,
            'status' => 'pending',
        ]);

        return back()->with('message', '修正申請を送信しました');
    }


    public function requestList(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        if ($tab === 'approved') {
        $statusValue = 'approved';
        } else {
            $tab = 'pending';
        $statusValue = 'pending';
        }

        $requests = StampCorrectionRequest::with('attendance')
            ->where('user_id', auth()->id())
            ->where('status', $statusValue)
            ->orderByDesc('updated_at')
            ->get();

        return view('request.list_request', compact('requests', 'tab'));
    }

    public function store(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        StampCorrectionRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => $attendance->id,
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'breaks' => $request->breaks,
            'note' => $request->note,
            'status' => 'pending',
        ]);

        return back()->with('message', '修正申請を送信しました');
    }

}
