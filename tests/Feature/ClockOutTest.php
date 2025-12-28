<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤中ユーザーは退勤すると退勤済になる()
    {
        Carbon::setTestNow(Carbon::create(2025, 12, 28, 18, 0, 0));

        $user = User::factory()->create();

        // 出勤中の状態を作る
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => null,
        ]);

        $this->actingAs($user);

        // 退勤ボタンが表示されている
        $response = $this->get('/attendance');
        $response->assertSee('退勤');

        // 退勤処理
        $this->post('/attendance/clock-out');

        // ステータスが退勤済になる
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }

    /** @test */
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::create(2025, 12, 28, 9, 0, 0));

        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤状態を作る
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        // 退勤（18:00）
        Carbon::setTestNow(Carbon::create(2025, 12, 28, 18, 0, 0));
        $this->post('/attendance/clock-out');

        // 勤怠一覧を確認
        $response = $this->get('/attendance/list');

        $response->assertSee('09:00'); // 出勤
        $response->assertSee('18:00'); // 退勤

        Carbon::setTestNow();
    }

}
