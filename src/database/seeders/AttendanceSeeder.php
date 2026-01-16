<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = User::orderBy('id')->take(3)->get();

        if ($users->count() < 1) {
            return;
        }

        $u1 = $users[0];
        $u2 = $users[1] ?? $users[0];
        $u3 = $users[2] ?? $users[0];

        $now = Carbon::now();

        Attendance::insert([
            [
                'user_id' => $u1->id,
                'work_date' => '2025-12-16',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'status' => '退勤済',
                'remark' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $u1->id,
                'work_date' => '2025-12-17',
                'start_time' => '10:00:00',
                'end_time' => null,
                'status' => '出勤中',
                'remark' => '打刻忘れ',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $u1->id,
                'work_date' => '2025-12-18',
                'start_time' => '09:30:00',
                'end_time' => '17:30:00',
                'status' => '退勤済',
                'remark' => '電車遅延のため',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $u2->id,
                'work_date' => '2025-12-16',
                'start_time' => '08:45:00',
                'end_time' => '17:15:00',
                'status' => '退勤済',
                'remark' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $u2->id,
                'work_date' => '2025-12-17',
                'start_time' => '09:00:00',
                'end_time' => '18:30:00',
                'status' => '退勤済',
                'remark' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $u3->id,
                'work_date' => '2025-12-16',
                'start_time' => '09:15:00',
                'end_time' => '18:05:00',
                'status' => '退勤済',
                'remark' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $u3->id,
                'work_date' => '2025-12-19',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'status' => '退勤済',
                'remark' => '備考あり',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}