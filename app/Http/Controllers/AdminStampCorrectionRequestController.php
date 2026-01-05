<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;

class AdminStampCorrectionRequestController extends Controller
{
    public function index(Request $request) {
        // tab: pending / approved
        $tab = $request->query('tab', 'pending');

        $query = StampCorrectionRequest::query()
            ->with(['user', 'attendance']);

        if ($tab === 'approved') {
            $query->where('status', 'approved');
        } else {
            $tab = 'pending';
            $query->where('status', 'pending');
        }

        $requests = $query->orderByDesc('created_at')->get();

        return view('admin.request_list', [
            'tab' => $tab,
            'requests' => $requests,
        ]);
    }

    public function approve(StampCorrectionRequest $stampCorrectionRequest)
    {
        $attendance = $stampCorrectionRequest->attendance;

        // 出退勤（Carbon化）
        $clockIn  = Carbon::createFromFormat('H:i', $stampCorrectionRequest->clock_in);
        $clockOut = Carbon::createFromFormat('H:i', $stampCorrectionRequest->clock_out);

        $attendance->update([
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'note' => $stampCorrectionRequest->note,
        ]);

        // 休憩を全削除 → 再作成
        $attendance->breakTimes()->delete();

        foreach ($stampCorrectionRequest->breaks as $break) {
            $attendance->breakTimes()->create([
                'break_start' => Carbon::createFromFormat('H:i', $break['start']),
                'break_end'   => Carbon::createFromFormat('H:i', $break['end']),
            ]);
        }

        // 🔥 ここが今まで無かった
        $attendance->load('breakTimes');

        // 休憩合計（分）
        $totalBreakMinutes = $attendance->breakTimes
            ->sum(fn ($b) => $b->break_start->diffInMinutes($b->break_end));

        $attendance->total_break = sprintf(
            '%02d:%02d',
            intdiv($totalBreakMinutes, 60),
            $totalBreakMinutes % 60
        );

        // 勤務時間（分）
        $workMinutes =
            $attendance->clock_in->diffInMinutes($attendance->clock_out)
            - $totalBreakMinutes;

        $attendance->total_work = sprintf(
            '%02d:%02d',
            intdiv(max(0, $workMinutes), 60),
            max(0, $workMinutes) % 60
        );

        $attendance->save();

        // ステータス更新
        $stampCorrectionRequest->update([
            'status' => 'approved',
        ]);

        return back()->with('approved', true);
    }

    public function show(StampCorrectionRequest $stampCorrectionRequest) {
        return view('admin.stamp_correction_request_show', [
                'request' => $stampCorrectionRequest,
            'attendance' => $stampCorrectionRequest->attendance,
            'user' => $stampCorrectionRequest->user,
        ]);
    }
}