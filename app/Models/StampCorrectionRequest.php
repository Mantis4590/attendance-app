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

        // 修正内容
        'clock_in',
        'clock_out',
        'breaks',
        'note',

        // 申請情報
        'status',
    ];

    protected $casts = [
        'breaks' => 'array',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function attendance() {
        return $this->belongsTo(Attendance::class);
    }
}
