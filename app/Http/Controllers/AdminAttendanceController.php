<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Models\User;

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

        return view('admin.attendance_show', [
            'attendance' => $attendance,
        ]);
    }

    public function update(AdminAttendanceUpdateRequest $request, $id) {
        $attendance = Attendance::findOrFail($id);

        // 保存処理
        $attendance->clock_in = $request->clock_in;
        $attendance->clock_out = $request->clock_out;
        $attendance->break_start = $request->break_start;
        $attendance->break_end = $request->break_end;
        $attendance->note = $request->note;

        // 合計時間の再計算
        $attendance->total_work = $this->calcWorkMinutes(
            $attendance->clock_in,
            $attendance->clock_out,
            $attendance->break_start,
            $attendance->break_end
        );

        $attendance->total_break = $this->calcBreakMinutes(
            $attendance->break_start,
            $attendance->break_end
        );

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
