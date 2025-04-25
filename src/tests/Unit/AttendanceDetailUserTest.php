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
        // 1. ユーザーを作成
        $user = User::factory()->create(['name' => 'テスト太郎']);

        // 2. 勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
        ]);

        // 3. ログインして勤怠詳細ページにアクセス
        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        // 4. レスポンスからHTMLを取得して整形
        $html = preg_replace('/\s+/', '', $response->getContent());

        // 5. 名前が含まれているか確認
        $this->assertStringContainsString('<divclass="content">テスト太郎</div>', $html);
    }

    /**
     * ✅ 2. 勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function test_attendance_detail_shows_correct_date()
    {
        // 1. ユーザーを作成
        $user = User::factory()->create();

        // 2. 日付を指定して勤怠データを作成
        $date = Carbon::parse('2024-10-01');
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $date,
        ]);

        // 3. ログインして勤怠詳細ページにアクセス
        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        // 4. HTML整形
        $html = preg_replace('/\s+/', '', $response->getContent());

        // 5. 日付（表示形式）を確認
        $expectedDate = $date->format('Y年n月j日');
        $this->assertStringContainsString("<divclass=\"content\">{$expectedDate}</div>", $html);
    }

    /**
     * ✅ 3. 「出勤・退勤」に表示される時間が勤怠データと一致している
     */
    public function test_attendance_detail_shows_correct_clock_in_and_out()
    {
        // 1. ユーザーと勤怠データを作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 2. ログインして勤怠詳細ページへアクセス
        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        // 3. HTML整形
        $html = preg_replace('/\s+/', '', $response->getContent());

        // 4. 出勤と退勤時刻が含まれるか確認
        $this->assertStringContainsString('<inputtype="time"name="clock_in"value="09:00">', $html);
        $this->assertStringContainsString('<inputtype="time"name="clock_out"value="18:00">', $html);
    }

    /**
     * ✅ 4. 「休憩」に表示される時間がBreakTimeと一致している
     */
    public function test_attendance_detail_shows_correct_break_times()
    {
        // 1. ユーザーと勤怠データを作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 2. 休憩時間を登録
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        // 3. ログインして勤怠詳細ページへアクセス
        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        // 4. HTML整形
        $html = preg_replace('/\s+/', '', $response->getContent());

        // 5. 休憩時間の確認
        $this->assertStringContainsString('<inputtype="time"name="break_start[]"value="12:00">', $html);
        $this->assertStringContainsString('<inputtype="time"name="break_end[]"value="13:00">', $html);
    }
}
