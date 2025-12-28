<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Admin;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者作成 & ログイン
        $this->admin = Admin::factory()->create();
        $this->actingAs($this->admin, 'admin');
    }

    /** @test */
    public function 管理者は全一般ユーザーの氏名とメールアドレスを確認できる()
    {
        $users = User::factory()->count(3)->create();

        $response = $this->get(route('admin.staff'));

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    /** @test */
    public function ユーザーの勤怠情報が正しく表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->startOfMonth(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->get(
            route('admin.attendance.staff', $user->id)
        );

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 前月ボタンで前月の勤怠が表示される()
    {
        $user = User::factory()->create();

        $prevMonth = now()->subMonth()->startOfMonth();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $prevMonth,
        ]);

        $response = $this->get(
            route('admin.attendance.staff', [
                'id' => $user->id,
                'month' => $prevMonth->format('Y-m')
            ])
        );

        $response->assertStatus(200);
        $response->assertSee($prevMonth->format('Y-m'));
    }

    /** @test */
    public function 翌月ボタンで翌月の勤怠が表示される()
    {
        $user = User::factory()->create();

        $nextMonth = now()->addMonth()->startOfMonth();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth,
        ]);

        $response = $this->get(
            route('admin.attendance.staff', [
                'id' => $user->id,
                'month' => $nextMonth->format('Y-m')
            ])
        );

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('Y-m'));
    }

    /** @test */
    public function 詳細ボタンでその日の勤怠詳細画面に遷移できる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->get(
            route('admin.attendance.show', $attendance->id)
        );

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }

}
