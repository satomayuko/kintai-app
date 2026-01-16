<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BreakSeeder extends Seeder
{
    public function run()
    {
        $attendances = DB::table('attendances')
            ->orderBy('id')
            ->get(['id', 'work_date', 'start_time']);

        $inserts = [];

        foreach ($attendances as $index => $a) {
            if (empty($a->start_time)) {
                continue;
            }

            $base = Carbon::parse($a->work_date . ' ' . $a->start_time);

            $pattern = $index % 4;

            if ($pattern === 0) {
                continue;
            }

            if ($pattern === 1) {
                $inserts[] = [
                    'attendance_id' => $a->id,
                    'break_start' => $base->copy()->addHours(2)->toDateTimeString(),
                    'break_end' => $base->copy()->addHours(2)->addMinutes(30)->toDateTimeString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                continue;
            }

            if ($pattern === 2) {
                $inserts[] = [
                    'attendance_id' => $a->id,
                    'break_start' => $base->copy()->addHours(3)->toDateTimeString(),
                    'break_end' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                continue;
            }

            $inserts[] = [
                'attendance_id' => $a->id,
                'break_start' => $base->copy()->addHours(2)->toDateTimeString(),
                'break_end' => $base->copy()->addHours(2)->addMinutes(15)->toDateTimeString(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $inserts[] = [
                'attendance_id' => $a->id,
                'break_start' => $base->copy()->addHours(5)->toDateTimeString(),
                'break_end' => $base->copy()->addHours(5)->addMinutes(10)->toDateTimeString(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($inserts)) {
            DB::table('breaks')->insert($inserts);
        }
    }
}