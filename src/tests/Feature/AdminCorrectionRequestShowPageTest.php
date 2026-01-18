<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminCorrectionRequestShowPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_correction_request_detail_page(): void
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $user = User::factory()->create();

        $attendanceId = DB::table('attendances')->insertGetId([
            'user_id'    => $user->id,
            'work_date'  => '2025-12-16',
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
            'status'     => 0,
            'remark'     => 'テスト勤怠',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $requestId = DB::table('stamp_correction_requests')->insertGetId([
            'user_id'         => $user->id,
            'attendance_id'   => $attendanceId,
            'corrected_start' => '09:00:00',
            'corrected_end'   => '18:00:00',
            'break1_start'    => '12:00:00',
            'break1_end'      => '13:00:00',
            'break2_start'    => '15:00:00',
            'break2_end'      => '15:15:00',
            'remark'          => '電車遅延のため',
            'status'          => 0,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->get("/admin/stamp_correction_request/{$requestId}")
            ->assertOk();
    }
}