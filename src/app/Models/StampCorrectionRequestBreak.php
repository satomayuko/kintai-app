<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequestBreak extends Model
{
    use HasFactory;

    protected $table = 'stamp_correction_request_breaks';

    protected $fillable = [
        'stamp_correction_request_id',
        'break_start',
        'break_end',
        'sort_order',
    ];

    public function stampCorrectionRequest()
    {
        return $this->belongsTo(StampCorrectionRequest::class, 'stamp_correction_request_id');
    }
}