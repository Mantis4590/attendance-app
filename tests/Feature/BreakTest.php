<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 休憩ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2025, 12, 28, 12, 0, 0));

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        $this->actingAs($user);

        // 休憩入ボタンが表示されている
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // 休憩入処理
        $this->post('/attendance/start-break');

        // ステータスが休憩中になる
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');

        Carbon::setTestNow();
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        $this->actingAs($user);

        // 1回目 休憩入 → 戻る
        $this->post('/attendance/start-break');
        $this->post('/attendance/end-break');

        // 再度 休憩入できる（ボタンが表示される）
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        $this->actingAs($user);

        // 休憩入
        $this->post('/attendance/start-break');

        // 休憩戻ボタンが表示されている
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        // 休憩戻処理
        $this->post('/attendance/end-break');

        // ステータスが出勤中になる
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        $this->actingAs($user);

        // 休憩入 → 休憩戻 → 再度休憩入
        $this->post('/attendance/start-break');
        $this->post('/attendance/end-break');

        $response = $this->get('/attendance');

        // 再度休憩入できることを確認
        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::create(2025, 12, 28, 12, 0, 0));

        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        // 休憩入
        $this->post('/attendance/start-break');

        // 30分後に休憩戻
        Carbon::setTestNow(Carbon::create(2025, 12, 28, 12, 30, 0));
        $this->post('/attendance/end-break');

        $response = $this->get('/attendance/list');

        $response->assertSee('12:00'); // 出勤
        $response->assertSee('00:30'); // 休憩時間

        Carbon::setTestNow();
    }

}
