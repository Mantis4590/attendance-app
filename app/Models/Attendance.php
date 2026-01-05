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
        'date',
        'clock_in',
        'clock_out',
        'total_break',
        'total_work',
        'note',
        'status'
    ];

    protected $casts = [
        'date'      => 'date',
        'clock_in'  => 'datetime:H:i',
        'clock_out' => 'datetime:H:i',
    ];

    /* =====================
        リレーション
    ====================== */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    /* =====================
        計算ロジック（分）
    ====================== */

    /**
     * 休憩合計（分）
     */
    public function getCalculatedTotalBreakMinutesAttribute(): int
    {
        if (!$this->relationLoaded('breakTimes')) {
            $this->load('breakTimes');
        }

        return $this->breakTimes
            ->filter(function ($breakTime) {
                return $breakTime
                    && $breakTime->break_start instanceof Carbon
                    && $breakTime->break_end instanceof Carbon;
            })
            ->sum(function ($breakTime) {
                return $breakTime->break_start
                    ->diffInMinutes($breakTime->break_end);
            });
    }

    /**
     * 勤務時間合計（分）
     */
    public function getCalculatedTotalWorkMinutesAttribute(): int
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        return max(
            0,
            $this->clock_in->diffInMinutes($this->clock_out)
            - $this->calculated_total_break_minutes
        );
    }

    /* =====================
        表示用（H:i）
    ====================== */

    public function getTotalBreakDisplayAttribute(): string
    {
        $breakMinutes = $this->calculated_total_break_minutes;

        return $breakMinutes > 0
            ? gmdate('H:i', $breakMinutes * 60)
            : '00:00';
    }

    public function getTotalWorkDisplayAttribute(): string
    {
        $workMinutes = $this->calculated_total_work_minutes;

        return $workMinutes > 0
            ? gmdate('H:i', $workMinutes * 60)
            : '00:00';
    }
}
