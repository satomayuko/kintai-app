<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendancePageTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
public function test_user_can_view_attendance_page(): void
    {
        $user = \App\Models\User::factory()->create();

        $this->actingAs($user)
            ->get('/attendance')
            ->assertStatus(200);
    }
}
