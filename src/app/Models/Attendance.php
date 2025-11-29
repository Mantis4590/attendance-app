<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
        'total_break',
        'total_work',
        'note',
        'status'
    ];

    public function breakTimes() {
        return $this->hasMany(BreakTime::class);
    }
}
