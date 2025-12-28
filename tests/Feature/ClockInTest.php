<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤務外ユーザーは出勤ボタンを押すと出勤中になる()
    {
        Carbon::setTestNow(Carbon::create(2025, 12, 28, 9, 0, 0));

        $user = User::factory()->create();
        $this->actingAs($user);

        // ① 出勤前は「出勤」ボタンが表示されている
        $response = $this->get('/attendance');
        $response->assertSee('出勤');

        // ② 出勤処理を行う
        $this->post('/attendance/clock-in');

        // ③ 出勤レコードが作成されている
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
        ]);

        // ④ ステータスが「出勤中」になる
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');

        Carbon::setTestNow();
    }

    /** @test */
    public function 出勤は1日1回のみできる()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        // 出勤ボタンは表示されない
        $response->assertDontSee('index__button-start');

        // 代わりに退勤・休憩入が表示される
        $response->assertSee('退勤');
        $response->assertSee('休憩入');
    }


    /** @test */
    public function 出勤時刻が勤怠一覧画面に表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 12, 28, 9, 0, 0));

        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤処理
        $this->post('/attendance/clock-in');

        // 勤怠一覧画面を開く
        $response = $this->get('/attendance/list');

        // 出勤時刻が表示されている
        $response->assertSee('09:00');

        Carbon::setTestNow();
    }

}
