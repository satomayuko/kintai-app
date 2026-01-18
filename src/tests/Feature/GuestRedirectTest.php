<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_root(): void
    {
        $this->get('/')
            ->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_user_pages(): void
    {
        $this->get('/attendance/list')
            ->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_admin_login_from_admin_pages(): void
    {
        $this->get('/admin/attendance/list')
            ->assertRedirect('/admin/login');
    }
}