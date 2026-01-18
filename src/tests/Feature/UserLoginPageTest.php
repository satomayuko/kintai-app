<?php

namespace Tests\Feature;

use Tests\TestCase;

class UserLoginPageTest extends TestCase
{
    public function test_login_page_can_be_displayed()
    {
        $this->get('/login')->assertStatus(200);
    }
}