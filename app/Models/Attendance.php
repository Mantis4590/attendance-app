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

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime:H:i',
        'clock_out' => 'datetime:H:i',
        'break_start' => 'datetime:H:i',
        'break_end' => 'datetime:H:i',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getTotalBreakDisplayAttribute() {
        if ($this->total_break === null) return '-';

        $minutes = $this->convertToMinutes($this->total_break);

        return gmdate('H:i', $minutes * 60);
    }

    public function getTotalWorkDisplayAttribute() {
        if ($this->total_work === null) return '-';

        $minutes = $this->convertToMinutes($this->total_work);

        return gmdate('H:i', $minutes * 60);
    }

    private function convertToMinutes($value) {
        if (is_numeric($value)) {
            return intval($value);
        }

        // "H:i:s" の文字列の場合
        if (is_string($value)) {
            $parts = explode(':', $value);

            if (count($parts) === 3) {
                $hour = intval($parts[0]);
                $minute = intval($parts[1]);
                $second = intval($parts[2]);

                return $hour * 60 + $minute + intval($second / 60);
            }
        }

        return 0;
    }

}
