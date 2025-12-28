<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
    {
        // ユーザー作成
        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        // 勤怠データ作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
        ]);

        // ログイン
        $this->actingAs($user);

        // 勤怠詳細画面にアクセス
        $response = $this->get("/attendance/detail/{$attendance->id}");

        // 確認
        $response->assertStatus(200);
        $response->assertSee('山田太郎');
    }

    /** @test */
    public function 勤怠詳細画面の「日付」が選択した日付になっている()
    {
        $user = User::factory()->create();

        $date = Carbon::create(2025, 12, 28);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(9, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertSee('2025年');
        $response->assertSee('12月28日');
    }

    /** @test */
    public function 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 「休憩」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => '12:30',
        ]);

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('value="12:00"', false);
        $response->assertSee('value="12:30"', false);
    }

}
