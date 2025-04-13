<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => 1, // Seeder側で適宜上書き
            'work_date' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'clock_in' => $this->faker->dateTimeBetween('09:00', '10:00'),
            'clock_out' => $this->faker->dateTimeBetween('17:00', '19:00'),
            'note' => $this->faker->sentence(),
            'status' => '退勤済', // 状態は任意（勤務外、出勤中、休憩中、退勤済）
        ];
    }
}
