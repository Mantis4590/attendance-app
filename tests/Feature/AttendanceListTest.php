<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自分が行った勤怠情報が全て表示されている()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        // 自分の勤怠（12/28）
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-12-28',
            'clock_in' => '2025-12-28 09:00:00',
            'clock_out' => '2025-12-28 18:00:00',
        ]);

        // 他人の勤怠（表示されない想定）
        Attendance::create([
            'user_id' => $other->id,
            'date' => '2025-12-28',
            'clock_in' => '2025-12-28 10:00:00',
            'clock_out' => '2025-12-28 19:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list?month=2025-12');

        // 自分の勤怠が表示される
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        // 他人の勤怠が表示されない（時刻で雑に判定）
        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');
    }

    /** @test */
    public function 勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 12, 15, 9, 0, 0));

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        // 画面の表示形式が「YYYY/MM」なのでそこに合わせる
        $response->assertSee('2025/12');

        Carbon::setTestNow();
    }

    /** @test */
    public function 前月を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-11-10',
            'clock_in' => '2025-11-10 08:00:00',
            'clock_out' => '2025-11-10 17:00:00',
        ]);

        $this->actingAs($user);

        // 「前月」押下は、結果として ?month=2025-11 に遷移する想定
        $response = $this->get('/attendance/list?month=2025-11');

        $response->assertSee('2025/11');
        $response->assertSee('08:00');
        $response->assertSee('17:00');
    }

    /** @test */
    public function 翌月を押下した時に表示月の翌月の情報が表示される()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-01-05',
            'clock_in' => '2026-01-05 10:00:00',
            'clock_out' => '2026-01-05 19:00:00',
        ]);

        $this->actingAs($user);

        // 「翌月」押下は、結果として ?month=2026-01 に遷移する想定
        $response = $this->get('/attendance/list?month=2026-01');

        $response->assertSee('2026/01');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-12-28',
            'clock_in' => '2025-12-28 09:00:00',
            'clock_out' => '2025-12-28 18:00:00',
        ]);

        $this->actingAs($user);

        // 一覧を開く
        $response = $this->get('/attendance/list?month=2025-12');

        // 一覧に「詳細」リンク（/attendance/detail/{id}）が存在すること
        $response->assertSee('/attendance/detail/' . $attendance->id);
        $response->assertSee('詳細');

        // 実際に詳細URLへ遷移して 200 になること（=遷移できる）
        $detail = $this->get('/attendance/detail/' . $attendance->id);
        $detail->assertStatus(200);
    }
}
