<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'attendance_id' => 1,
            'request_date' => now()->subDays(rand(1, 20)),
            'clock_in' => now()->setTime(9, 0),  // 出勤時刻（申請内容）：午前9時ちょうど
            'clock_out' => now()->setTime(18, 0), // 退勤時刻（申請内容）：午後6時ちょうど
            'note' => '打刻漏れのため申請',
            'status' => $this->faker->randomElement(['承認待ち', '承認済']),
            'approved_by' => null,
            'approved_at' => null,
        ];
    }
}
