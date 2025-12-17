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
        // 現在日時
        $now = now();

        // 曜日を日本語に変換するための配列
        $weekMap = [
            'Mon' => '月',
            'Tue' => '火',
            'Wed' => '水',
            'Thu' => '木',
            'Fri' => '金',
            'Sat' => '土',
            'Sun' => '日',
        ];

        // 英語の曜日（Mon, Tue...）
        $weekEng = $now->format('D');
        // 日本語へ変換
        $weekJp = $weekMap[$weekEng];

        // 表示用の日付
        $nowDate = $now->format("Y年n月j日({$weekJp})");

        // 時刻
        $nowTime = $now->format('H:i');

        // 今日の勤怠データ取得
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', $now->toDateString())
            ->first();

        // ステータス判定
        if (!$attendance) {
            $status = "勤務外";
        } else {
            // 最新の休憩レコード
            $latestBreak = $attendance->breakTimes()->latest()->first();

            if ($attendance->clock_out) {
            $status = "退勤済み";
            } elseif ($latestBreak && is_null($latestBreak->break_end)) {
                // break_end が null → 休憩中
                $status = "休憩中";
            } elseif ($attendance->clock_in) {
            $status = "出勤中";
            } else {
                $status = "勤務外";
            }
        }

        return view('attendance.index', compact('nowDate', 'nowTime', 'status', 'attendance'));
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

    public function startBreak() {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', now()->toDateString())
            ->first();

        // 新しい休憩レコードを追加
        $attendance->breakTimes()->create([
            'break_start' => now(),
        ]);

        // 状態変更
        $attendance->update([
            'status' => '休憩中'
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
        $hasPendingRequest = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();

        return view('attendance.detail',compact('attendance', 'user', 'hasPendingRequest'));
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        // 出勤・退勤の更新
        $attendance->clock_in  = $request->clock_in ? Carbon::parse($request->clock_in) : null;
        $attendance->clock_out = $request->clock_out ? Carbon::parse($request->clock_out) : null;

        // 既存の休憩時間を更新
        foreach ($attendance->breakTimes as $index => $break) {

            if (isset($request->breaks[$index])) {
                $start = $request->breaks[$index]['start'] ?? null;
                $end   = $request->breaks[$index]['end'] ?? null;

                $break->break_start = $start ? Carbon::parse($start) : null;
                $break->break_end   = $end   ? Carbon::parse($end)   : null;
                $break->save();
            }
        }

        // 新しい休憩行があれば追加
        if (!empty($request->breaks['new']['start']) || !empty($request->breaks['new']['end'])) {
            $attendance->breakTimes()->create([
                'break_start' => $request->breaks['new']['start'] ? Carbon::parse($request->breaks['new']['start']) : null,
                'break_end'   => $request->breaks['new']['end']   ? Carbon::parse($request->breaks['new']['end'])   : null,
            ]);
        }

        // 備考の更新
        $attendance->note = $request->note;

        // 休憩合計を再計算
        $attendance->load('breakTimes');

        $totalBreakMinutes = $attendance->breakTimes
            ->filter(fn($b) => $b->break_start && $b->break_end)
            ->sum(fn($b) => $b->break_start->diffInMinutes($b->break_end));

        $attendance->total_break = sprintf('%02d:%02d',
            intdiv($totalBreakMinutes, 60),
            $totalBreakMinutes % 60
        );

        // 勤務時間も再計算
        if ($attendance->clock_in && $attendance->clock_out) {
            $workMinutes = $attendance->clock_in->diffInMinutes($attendance->clock_out)
            - $totalBreakMinutes;

            if ($workMinutes < 0) $workMinutes = 0;

            $attendance->total_work = sprintf('%02d:%02d',
            intdiv($workMinutes, 60),
            $workMinutes % 60
            );
        }

        // 保存
        $attendance->save();

        // ★ 修正申請を作成 ★
        StampCorrectionRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => $attendance->id,
            'reason' => $request->note, // 申請理由
            'status' => 'pending',
        ]);

        return redirect()->route('attendance.detail', ['id' => $id])
        ->with('success', '修正申請を送信しました');

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

}
