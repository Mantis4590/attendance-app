<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;

class AdminStampCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ログイン
        $this->admin = Admin::factory()->create();
        $this->actingAs($this->admin, 'admin');
    }

    /** @test */
    public function 承認待ちの修正申請が全て表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $pendingRequest = StampCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'status' => 'pending',
        ]);

        $response = $this->get(
            route('admin.stamp_correction_request.list')
        );

        $response->assertStatus(200);
        $response->assertSee((string) $pendingRequest->id);
    }

    /** @test */
    public function 承認済みの修正申請が全て表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $approvedRequest = StampCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'status' => 'approved',
        ]);

        $response = $this->get(
            route('admin.stamp_correction_request.list', ['status' => 'approved'])
        );

        $response->assertStatus(200);
        $response->assertSee((string) $approvedRequest->id);
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $request = StampCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'status' => 'pending',
        ]);

        $response = $this->get(
            route('admin.stamp_correction_request.show', $request->id)
        );

        $response->assertStatus(200);
        $response->assertSee('修正申請');
    }

    /** @test */
    public function 修正申請を承認すると勤怠情報が更新される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $request = StampCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'status' => 'pending',
        ]);

        $response = $this->post(
            route('admin.stamp_correction_request.approve', $request->id)
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
        ]);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);
    }

}
