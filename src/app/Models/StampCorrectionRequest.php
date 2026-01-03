<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'attendance_id',
    'corrected_start',
    'corrected_end',
    'break1_start',
    'break1_end',
    'break2_start',
    'break2_end',
    'remark',
    'status',
];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
