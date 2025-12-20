<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Models\User;
use App\Models\StampCorrectionRequest;

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

    public function update(AdminAttendanceUpdateRequest $request, $id) {
        $attendance = Attendance::findOrFail($id);

        // 出勤・退勤・備考
        $attendance->update([
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'note' => $request->note,
        ]);

        // 既存の休憩を全削除
        $attendance->breakTimes()->delete();

        // 休憩を再作成
        if ($request->filled('breaks')) {
            foreach ($request->breaks as $break) {
                if (
                    !empty($break['start']) &&
                    !empty($break['end'])
                ) {
                    $attendance->breakTimes()->create([
                        'break_start' => $break['start'],
                        'break_end' => $break['end'],
                    ]);
                }
            }
        }

        // リレーションを再読み込み
        $attendance->load('breakTimes');

        // 休憩合計（分）を再計算
        $totalBreakMinutes = $attendance->breakTimes
            ->filter(fn ($breakTime) => $breakTime->break_start && $breakTime->break_end
            )
            ->sum(fn ($breakTime) => $breakTime->break_start->diffInMinutes($breakTime->break_end)
            );

        $attendance->total_break = sprintf(
            '%02d:%02d',
            intdiv($totalBreakMinutes, 60),
            $totalBreakMinutes % 60
        );

        // 勤務時間の再計算
        if ($attendance->clock_in && $attendance->clock_out) {
            $workMinutes =
                $attendance->clock_in->diffInMinutes($attendance->clock_out)
                - $totalBreakMinutes;
                
            $workMinutes = max(0, $workMinutes);

            $attendance->total_work = sprintf(
                '%02d:%02d',
                intdiv($workMinutes, 60),
                $workMinutes % 60
            );
        }

        $attendance->save();

        return back()->with('success', '勤怠内容を修正しました');
    }

    // 出勤〜退勤の合同勤務時間（分）
    private function calcWorkMinutes($in, $out, $breakStart, $breakEnd) {
        if (!$in || !$out) {
            return null;
        }

        $start = strtotime($in);
        $end = strtotime($out);
        $work = $end - $start;

        // 休憩があれば引く
        if ($breakStart && $breakEnd) {
            $bStart = strtotime($breakStart);
            $bEnd = strtotime($breakEnd);
            $work -= max(0, $bEnd - $bStart);
        }

        return max(0, intdiv($work, 60));
    }

    // 休憩合計時間（分）
    private function calcBreakMinutes($breakStart, $breakEnd) {
        if (!$breakStart || !$breakEnd) {
            return null;
        }

        $bStart = strtotime($breakStart);
        $bEnd = strtotime($breakEnd);
        $break = max(0, $bEnd - $bStart);

        return intdiv($break, 60);
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
}
