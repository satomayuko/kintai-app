<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCorrectionRequestApproveTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_correction_request(): void
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-12-16',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'status' => 0,
        ]);

        $request = StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'corrected_start' => '09:00:00',
            'corrected_end' => '18:00:00',
            'break1_start' => '12:00:00',
            'break1_end' => '13:00:00',
            'break2_start' => null,
            'break2_end' => null,
            'remark' => '電車遅延のため',
            'status' => 0,
        ]);

        $response = $this->post("/admin/stamp_correction_request/approve/{$request->id}");

        $response->assertStatus(302);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $request->id,
            'status' => 1,
        ]);
    }
}