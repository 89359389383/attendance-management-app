<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceDetailUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 1. 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     */
    public function test_attendance_detail_shows_logged_in_user_name()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['name' => 'テスト太郎']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");
        $html = preg_replace('/\s+/', '', $response->getContent());

        // 修正済み：実際のHTML構造に基づいたアサート
        $this->assertStringContainsString('<spanclass="user-name">テスト太郎</span>', $html);
    }

    /**
     * ✅ 2. 勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function test_attendance_detail_shows_correct_date()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $date = Carbon::parse('2024-10-01');
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $date,
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");
        $html = preg_replace('/\s+/', '', $response->getContent());

        // 修正済み：<span class="year">2024年</span><span class="month-day">10月1日</span> に一致させる
        $expectedYear = $date->format('Y年');
        $expectedMonthDay = $date->format('n月j日');

        $this->assertStringContainsString("<spanclass=\"year\">{$expectedYear}</span><spanclass=\"month-day\">{$expectedMonthDay}</span>", $html);
    }

    /**
     * ✅ 3. 「出勤・退勤」に表示される時間が勤怠データと一致している
     */
    public function test_attendance_detail_shows_correct_clock_in_and_out()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");
        $html = preg_replace('/\s+/', '', $response->getContent());

        $this->assertStringContainsString('<inputtype="time"name="clock_in"value="09:00">', $html);
        $this->assertStringContainsString('<inputtype="time"name="clock_out"value="18:00">', $html);
    }

    /**
     * ✅ 4. 「休憩」に表示される時間がBreakTimeと一致している
     */
    public function test_attendance_detail_shows_correct_break_times()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");
        $html = preg_replace('/\s+/', '', $response->getContent());

        $this->assertStringContainsString('<inputtype="time"name="break_start[]"value="12:00">', $html);
        $this->assertStringContainsString('<inputtype="time"name="break_end[]"value="13:00">', $html);
    }
}
