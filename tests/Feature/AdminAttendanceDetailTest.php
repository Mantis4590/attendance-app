<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();

        $user = User::factory()->create();

        $this->attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => 'テスト',
        ]);
    }


    /** @test */
    public function 勤怠詳細画面に表示されるデータが選択したものと一致している()
    {
        $admin = Admin::factory()->create();

        $user = User::factory()->create([
            'name' => 'テスト太郎',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('テスト太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合エラーになる()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->patch(
            route('admin.attendance.update', $this->attendance->id),
            [
                'clock_in' => '18:00',
                'clock_out' => '09:00',
                'note' => 'テスト',
            ]
        );

        $response->assertSessionHasErrors(['clock_in']);
    }


    /** @test */
    public function 休憩開始時間が退勤時間より後の場合エラーになる()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->patch(
            route('admin.attendance.update', $this->attendance->id),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['start' => '19:00', 'end' => '19:30'],
                ],
                'note' => 'テスト',
            ]
        );

        $response->assertSessionHasErrors(['breaks']);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合エラーになる()
    {
        // 管理者ログイン
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 勤怠データ作成（← これが抜けてた）
        $attendance = Attendance::factory()->create([
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        // 実行
        $response = $this->patch(
            route('admin.attendance.update', $attendance->id),
            [
                'clock_in'  => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['start' => '17:00', 'end' => '19:00'], // 退勤後
                ],
                'note' => 'テスト',
            ]
        );

        // 検証（文言は見ない）
        $response->assertSessionHasErrors(['breaks']);
    }

    /** @test */
    public function 備考欄が未入力の場合エラーになる()
    {
        $admin = Admin::factory()->create();
        $attendance = Attendance::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->patch("/admin/attendance/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => '',
        ]);

        $response->assertSessionHasErrors(['note']);
    }

}