<?php

use Illuminate\Database\Eloquent\Factories\Factory;

class RequestBreakTimeFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('12:00', '13:00');
        return [
            'attendance_id' => 1,
            'break_start' => $start,
            'break_end' => (clone $start)->modify('+1 hour'),
        ];
    }
}
