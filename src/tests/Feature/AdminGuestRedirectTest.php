<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminGuestRedirectTest extends TestCase
{
    public function test_guest_is_redirected_from_admin_attendance_list(): void
    {
        $this->get('/admin/attendance/list')
            ->assertRedirect('/admin/login');
    }
}