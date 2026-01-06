<?php

namespace App\Http\Controllers;

use App\Models\StampCorrectionRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminStampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $query = StampCorrectionRequest::with(['user', 'attendance']);

        if ($tab === 'approved') {
            $query->where('status', 'approved');
        } else {
            $tab = 'pending';
            $query->where('status', 'pending');
        }

        return view('admin.request_list', [
            'tab' => $tab,
            'requests' => $query->orderByDesc('created_at')->get(),
        ]);
    }

    public function approve(StampCorrectionRequest $stampCorrectionRequest)
    {
        $attendance = $stampCorrectionRequest->attendance;

        // ✅ フォーマットを気にしない（これが肝）
        $clockIn  = Carbon::parse($stampCorrectionRequest->clock_in);
        $clockOut = Carbon::parse($stampCorrectionRequest->clock_out);

        $attendance->update([
            'clock_in'  => $clockIn,
            'clock_out' => $clockOut,
            'note'      => $stampCorrectionRequest->note,
        ]);

        // 休憩を全削除 → 再作成
        $attendance->breakTimes()->delete();

        foreach ($stampCorrectionRequest->breaks as $break) {
            $attendance->breakTimes()->create([
                'break_start' => Carbon::parse($break['start']),
                'break_end'   => Carbon::parse($break['end']),
            ]);
        }

        // 再計算
        $attendance->load('breakTimes');

        $totalBreakMinutes = $attendance->breakTimes
            ->sum(fn ($breakTime) =>
                $breakTime->break_start->diffInMinutes($breakTime->break_end)
            );

        $attendance->total_break = sprintf(
            '%02d:%02d',
            intdiv($totalBreakMinutes, 60),
            $totalBreakMinutes % 60
        );

        $workMinutes =
            $attendance->clock_in->diffInMinutes($attendance->clock_out)
            - $totalBreakMinutes;

        $attendance->total_work = sprintf(
            '%02d:%02d',
            intdiv(max(0, $workMinutes), 60),
            max(0, $workMinutes) % 60
        );

        $attendance->save();

        $stampCorrectionRequest->update([
            'status' => 'approved',
        ]);

        return back();
    }

    public function show(StampCorrectionRequest $stampCorrectionRequest)
    {
        return view('admin.stamp_correction_request_show', [
            'request'    => $stampCorrectionRequest,
            'attendance' => $stampCorrectionRequest->attendance,
            'user'       => $stampCorrectionRequest->user,
        ]);
    }
}
