<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;

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

    public function approve(StampCorrectionRequest $stampCorrectionRequest) {
        // 勤怠データ取得
        $attendance = $stampCorrectionRequest->attendance;

        // 出退勤を更新
        $attendance->update([
            'clock_in' => $stampCorrectionRequest->clock_in,
            'clock_out' => $stampCorrectionRequest->clock_out,
            'note' => $stampCorrectionRequest->note,
        ]);

        // 休憩を更新;
        $attendance->breakTimes()->delete();

        foreach ($stampCorrectionRequest->breaks as $break) {
            $attendance->breakTimes()->create([
                'break_start' => $break['start'],
                'break_end' => $break['end'],
            ]);
        }

        // 申請ステータス更新
        $stampCorrectionRequest->update([
            'status' => 'approved',
        ]);

        // 同じ画面に戻す（遷移なし）
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