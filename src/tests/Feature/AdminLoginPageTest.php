<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminLoginPageTest extends TestCase
{
    public function test_admin_login_page_can_be_displayed()
    {
        $this->get('/admin/login')->assertStatus(200);
    }
}