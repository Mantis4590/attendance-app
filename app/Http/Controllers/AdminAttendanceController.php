<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Models\User;
use App\Models\StampCorrectionRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // パラメータ date が無ければ今日
        $targetDate = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        // 前日・翌日
        $prevDate = $targetDate->copy()->subDay();
        $nextDate = $targetDate->copy()->addDay();

        // この日の前ユーザーの勤怠を取得
        $attendances = Attendance::with('user')
            ->whereDate('date', $targetDate)
            ->get();

        return view('admin.attendance_list', [
            'targetDate' => $targetDate,
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
            'attendances' => $attendances,
        ]);
    }

    public function show($id) {
        $attendance = Attendance::with(['user', 'breakTimes'])->findOrFail($id);

        // 承認待ちの修正申請があるか
        $hasPendingRequest = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();

        return view('admin.attendance_show', [
            'attendance' => $attendance,
            'hasPendingRequest' => $hasPendingRequest,
        ]);
    }

    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // ✅ 出勤・退勤は必ず Carbon にする
        $clockIn  = Carbon::createFromFormat('H:i', $request->clock_in);
        $clockOut = Carbon::createFromFormat('H:i', $request->clock_out);

        // 出勤・退勤・備考
        $attendance->update([
            'clock_in'  => $clockIn,
            'clock_out' => $clockOut,
            'note'      => $request->note,
        ]);

        // 既存の休憩を全削除
        $attendance->breakTimes()->delete();

        // ✅ 修正②：休憩も Carbon で保存
        if ($request->filled('breaks')) {
            foreach ($request->breaks   as $break) {
                if (!empty($break['start']) && !empty($break['end'])) {
                    $attendance->breakTimes()->create([
                        'break_start' => Carbon::createFromFormat('H:i', $break['start']),
                        'break_end'   => Carbon::createFromFormat('H:i', $break['end']),
                    ]);
                }
            }
        }

        // リレーション再読み込み
        $attendance->load('breakTimes');

        // ✅ 修正③：休憩合計（分）
        $totalBreakMinutes = $attendance->breakTimes
            ->sum(fn ($b) => $b->break_start->diffInMinutes($b->break_end));

        $attendance->total_break = sprintf(
            '%02d:%02d',
            intdiv($totalBreakMinutes, 60),
            $totalBreakMinutes % 60
        );

        // ✅ 勤務時間（分）
        $workMinutes =
            $attendance->clock_in->diffInMinutes($attendance->clock_out)
            - $totalBreakMinutes;

        $workMinutes = max(0, $workMinutes);

        $attendance->total_work = sprintf(
            '%02d:%02d',
            intdiv($workMinutes, 60),
            $workMinutes % 60
        );

        $attendance->save();

        return back()->with('success', '勤怠内容を修正しました');
    }

    public function staff(Request $request, $id) {
        $staff = User::findOrFail($id);

        // 対象月
        $month = $request->query('month')
            ? Carbon::parse($request->query('month'))
            : Carbon::now();
        
        // 月初〜月末
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $staff->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(fn ($a) => $a->date->format('Y-m-d'));

        $days = [];
        $current = $startOfMonth->copy();

        while ($current <= $endOfMonth) {
            $dateKey = $current->format('Y-m-d');

            $days[] = [
                'date' => $current->copy(),
                'attendance' => $attendances->get($dateKey),
            ];

            $current->addDay();
        }
        return view('admin.attendance_staff', [
            'staff' => $staff,
            'month' => $month,
            'days' => $days,
        ]);
    }

    public function csv(Request $request, $id) {
        $staff = User::findOrFail($id);

        // 対象月
        $month = $request->query('month')
            ? Carbon::parse($request->query('month'))
            : Carbon::now();

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $staff->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date')
            ->get()
            ->KeyBy(fn ($attendance) => $attendance->date->format('Y-m-d'));

        $response = new StreamedResponse(function () use (
            $staff,
            $month,
            $attendances,
            $startOfMonth,
            $endOfMonth
        ) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            // CSVヘッダ
            fputcsv($handle, [
                '日付',
                '出勤',
                '退勤',
                '休憩',
                '勤務時間',
            ]);

            $current = $startOfMonth->copy();

            while ($current <= $endOfMonth) {
                $key = $current->format('Y-m-d');
                $attendance = $attendances->get($key);

                fputcsv($handle, [
                    $current->format('Y-m-d'),
                    $attendance?->clock_in?->format('H:i') ?? '',
                    $attendance?->clock_out?->format('H:i') ?? '',
                    $attendance->total_break_display ?? '',
                    $attendance->total_work_display ?? '',
                ]);

                $current->addDay();
            }
        });

        $fileName = sprintf(
            '%s_%s_attendance.csv',
            $staff->name,
            $month->format('Y_m')
        );

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set(
            'Content-Disposition',
            "attachment; filename=\"{$fileName}\""
        );

        return $response;
    }
}
