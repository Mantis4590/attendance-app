<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StampCorrectionRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => \App\Models\Attendance::factory(),
            'user_id' => \App\Models\User::factory(),
            'status' => 'pending',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['start' => '12:00', 'end' => '12:30'],
            ],
            'note' => 'テスト修正',
        ];
    }

}
