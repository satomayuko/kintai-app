<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StampCorrectionRequestSeeder extends Seeder
{
    public function run()
    {
        $taroId = DB::table('users')->where('email', 'taro@example.com')->value('id');
        $hanakoId = DB::table('users')->where('email', 'hanako@example.com')->value('id');
        $jiroId = DB::table('users')->where('email', 'jiro@example.com')->value('id');

        $taroAttendance1 = DB::table('attendances')->where('user_id', $taroId)->orderBy('work_date', 'asc')->value('id');
        $hanakoAttendance1 = DB::table('attendances')->where('user_id', $hanakoId)->orderBy('work_date', 'asc')->value('id');
        $jiroAttendance1 = DB::table('attendances')->where('user_id', $jiroId)->orderBy('work_date', 'asc')->value('id');

        if ($taroAttendance1) {
            DB::table('stamp_correction_requests')->insert([
                'user_id' => $taroId,
                'attendance_id' => $taroAttendance1,
                'corrected_start' => '09:05:00',
                'corrected_end' => '18:00:00',
                'break1_start' => null,
                'break1_end' => null,
                'break2_start' => null,
                'break2_end' => null,
                'remark' => '打刻忘れ',
                'status' => '承認待ち',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($hanakoAttendance1) {
            DB::table('stamp_correction_requests')->insert([
                'user_id' => $hanakoId,
                'attendance_id' => $hanakoAttendance1,
                'corrected_start' => '10:00:00',
                'corrected_end' => '19:10:00',
                'break1_start' => '12:10:00',
                'break1_end' => '13:00:00',
                'break2_start' => null,
                'break2_end' => null,
                'remark' => '退勤打刻漏れ',
                'status' => '承認',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($jiroAttendance1) {
            DB::table('stamp_correction_requests')->insert([
                'user_id' => $jiroId,
                'attendance_id' => $jiroAttendance1,
                'corrected_start' => '08:30:00',
                'corrected_end' => '17:30:00',
                'break1_start' => '11:30:00',
                'break1_end' => '12:10:00',
                'break2_start' => '15:10:00',
                'break2_end' => '15:20:00',
                'remark' => '休憩時間修正',
                'status' => '却下',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}