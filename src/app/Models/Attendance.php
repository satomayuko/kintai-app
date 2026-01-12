<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'start_time',
        'end_time',
        'status',
        'remark',
    ];

    protected $casts = [
        'work_date'  => 'date',
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(WorkBreak::class, 'attendance_id');
    }

    public function correctionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    public function getTotalBreakMinutesAttribute()
    {
        return $this->breaks->reduce(function ($carry, $break) {
            if ($break->break_start && $break->break_end) {
                return $carry + Carbon::parse($break->break_start)
                    ->diffInMinutes(Carbon::parse($break->break_end));
            }
            return $carry;
        }, 0);
    }

    public function getBreakTimeForDisplayAttribute()
    {
        $minutes = $this->total_break_minutes;

        if ($minutes === 0) {
            return null;
        }

        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return sprintf('%d:%02d', $h, $m);
    }

    public function getWorkTimeForDisplayAttribute()
    {
        if (! $this->start_time || ! $this->end_time) {
            return null;
        }

        $total = Carbon::parse($this->start_time)
            ->diffInMinutes(Carbon::parse($this->end_time));

        $total -= $this->total_break_minutes;

        if ($total < 0) {
            $total = 0;
        }

        $h = intdiv($total, 60);
        $m = $total % 60;

        return sprintf('%d:%02d', $h, $m);
    }

    public function getBreakTimeDisplayAttribute()
    {
        return $this->break_time_for_display;
    }

    public function getWorkTimeDisplayAttribute()
    {
        return $this->work_time_for_display;
    }
}