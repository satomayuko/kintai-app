<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCorrectionRequestListPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_correction_request_list_page(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/stamp_correction_request/list');

        $response->assertStatus(200);
    }
}