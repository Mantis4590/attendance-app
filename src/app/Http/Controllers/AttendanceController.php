<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

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
            // レコード無し → 未出勤
            $status = "勤務外";
        } elseif ($attendance->clock_in && !$attendance->break_start && !$attendance->clock_out) {
            $status = "出勤中";
        } elseif ($attendance->break_start && !$attendance->break_end) {
            $status = "休憩中";
        } elseif ($attendance->break_start && $attendance->break_end && !$attendance->clock_out) {
            $status = "出勤中"; // 休憩から戻った後の状態
        } elseif ($attendance->clock_out) {
            $status = "退勤済み";
        } else {
            $status = "勤務外";
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
            ->where('date', now()->toDateString())
            ->first();

        // 終了していない最新の休憩を取得
        $break = $attendance->breakTimes()
            ->whereNull('break_end')
            ->latest()
            ->first();

        if ($break) {
            $break->update([
            'break_end' => now(),
            ]);
        }

        // 状態を出勤中へ
        $attendance->update(['status' => '出勤中']);

        return back();
    }

    public function clockOut() {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', now()->toDateString())
            ->first();

        $attendance->update([
            'clock_out' => now(),
            'status' => '退勤済み',
        ]);

        return back();
    }

    public function list() {
        return view('attendance.list');
    }

    public function detail($id) {
        return view('attendance.detail', [
            'id' => '$id'
        ]);
    }
}
