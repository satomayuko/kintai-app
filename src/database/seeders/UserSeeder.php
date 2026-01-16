<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            ['name' => 'テスト太郎', 'email' => 'taro@example.com'],
            ['name' => 'テスト花子', 'email' => 'hanako@example.com'],
            ['name' => 'テスト次郎', 'email' => 'jiro@example.com'],
        ];

        foreach ($users as $u) {
            DB::table('users')->updateOrInsert(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'remember_token' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}