<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者はその日の全ユーザーの勤怠情報を確認できる()
    {
        $admin = Admin::factory()->create();

        $users = User::factory()->count(2)->create();

        foreach ($users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => now()->toDateString(),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
            ]);
        }

        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
        }
    }

    /** @test */
    public function 遷移した際に現在の日付が表示される()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin');

        $today = now()->format('Y/m/d');

        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($today);
    }

    /** @test */
    public function 前日ボタンで前日の勤怠情報が表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $yesterday = now()->subDay()->toDateString();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $yesterday,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get("/admin/attendance/list?date={$yesterday}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('10:00');
    }

    /** @test */
    public function 翌日ボタンで翌日の勤怠情報が表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $tomorrow = now()->addDay()->toDateString();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $tomorrow,
            'clock_in' => '08:30',
            'clock_out' => '17:30',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get("/admin/attendance/list?date={$tomorrow}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('08:30');
    }

}

