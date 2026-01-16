<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        DB::table('admins')->updateOrInsert(
            ['email' => 'admin@example.com'],
            [
                'name' => '管理者太郎',
                'password' => Hash::make('password123'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}