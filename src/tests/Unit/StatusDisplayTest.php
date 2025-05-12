<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class StatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 1. ステータスが「勤務外」の場合に「勤務外」と表示される
     */
    public function test_status_display_as_off_duty()
    {
        // 1. ユーザーを作成
        $user = User::factory()->create()->first();

        // 2. ログイン
        $this->actingAs($user);

        // 3. 出勤前の勤怠データ（作成しない＝勤務外）状態で表示確認
        $response = $this->get(route('attendance.show'));

        // 4. ステータスが「勤務外」と表示されているか確認
        $response->assertSeeText('勤務外');
    }

    /**
     * ✅ 2. ステータスが「出勤中」の場合に「出勤中」と表示される
     */
    public function test_status_display_as_clocked_in()
    {
        // 1. ユーザーと勤怠データを作成（出勤中）
        $user = User::factory()->create()->first();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => now(),
            'status' => '出勤中',
        ]);

        // 2. ログイン
        $this->actingAs($user);

        // 3. 勤怠打刻画面にアクセス
        $response = $this->get(route('attendance.show'));

        // 4. ステータスが「出勤中」と表示されているか確認
        $response->assertSeeText('出勤中');
    }

    /**
     * ✅ 3. ステータスが「休憩中」の場合に「休憩中」と表示される
     */
    public function test_status_display_as_on_break()
    {
        // 1. ユーザーと勤怠データを作成（休憩中）
        $user = User::factory()->create()->first();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => now()->subHours(2),
            'status' => '休憩中',
        ]);

        // 2. ログイン
        $this->actingAs($user);

        // 3. 勤怠打刻画面にアクセス
        $response = $this->get(route('attendance.show'));

        // 4. ステータスが「休憩中」と表示されているか確認
        $response->assertSeeText('休憩中');
    }

    /**
     * ✅ 4. ステータスが「退勤済」の場合に「退勤済」と表示される
     */
    public function test_status_display_as_clocked_out()
    {
        // 1. ユーザーと勤怠データを作成（退勤済）
        $user = User::factory()->create()->first();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
            'status' => '退勤済',
        ]);

        // 2. ログイン
        $this->actingAs($user);

        // 3. 勤怠打刻画面にアクセス
        $response = $this->get(route('attendance.show'));

        // 4. ステータスが「退勤済」と表示されているか確認
        $response->assertSeeText('退勤済');
    }
}
