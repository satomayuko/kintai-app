<?php

namespace Tests\Feature;

use Tests\TestCase;

class AttendanceGuestRedirectTest extends TestCase
{
    public function test_guest_is_redirected_from_attendance_page(): void
    {
        $this->get('/attendance')
            ->assertRedirect('/login');
    }
}