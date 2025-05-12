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
        // 1. ユーザーを作成し、名前を設定
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['name' => 'テスト太郎']);

        // 2. 作成したユーザーに関連付けた勤怠情報を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
        ]);

        // 3. 勤怠詳細ページを開く
        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        // 4. ページのHTMLを整理して空白を削除
        $html = preg_replace('/\s+/', '', $response->getContent());

        // 5. ログインユーザーの名前「テスト太郎」が表示されていることを確認
        $this->assertStringContainsString('<spanclass="user-name">テスト太郎</span>', $html);
    }

    /**
     * ✅ 2. 勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function test_attendance_detail_shows_correct_date()
    {
        // 1. ユーザーを作成
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. 勤怠データに日付（2024年10月1日）を設定
        $date = Carbon::parse('2024-10-01');
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $date,
        ]);

        // 3. 勤怠詳細ページを開く
        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        // 4. ページのHTMLを整理して空白を削除
        $html = preg_replace('/\s+/', '', $response->getContent());

        // 5. ページ内の日付表示が選択した日付（2024年10月1日）と一致することを確認
        $expectedYear = $date->format('Y年');
        $expectedMonthDay = $date->format('n月j日');

        // 6. 期待通りのHTMLに年・月日が含まれていることを確認
        $this->assertStringContainsString("<spanclass=\"year\">{$expectedYear}</span><spanclass=\"month-day\">{$expectedMonthDay}</span>", $html);
    }

    /**
     * ✅ 3. 「出勤・退勤」に表示される時間が勤怠データと一致している
     */
    public function test_attendance_detail_shows_correct_clock_in_and_out()
    {
        // 1. ユーザーを作成
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. 出勤時間（09:00:00）と退勤時間（18:00:00）を設定した勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 3. 勤怠詳細ページを開く
        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        // 4. ページのHTMLを整理して空白を削除
        $html = preg_replace('/\s+/', '', $response->getContent());

        // 5. 出勤時間（09:00）と退勤時間（18:00）が正しく表示されていることを確認
        $this->assertStringContainsString('<inputtype="time"name="clock_in"value="09:00">', $html);
        $this->assertStringContainsString('<inputtype="time"name="clock_out"value="18:00">', $html);
    }

    /**
     * ✅ 4. 「休憩」に表示される時間がBreakTimeと一致している
     */
    public function test_attendance_detail_shows_correct_break_times()
    {
        // 1. ユーザーを作成
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. 勤怠データを作成し、休憩開始（12:00）と終了（13:00）の時間を設定
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        // 3. 勤怠詳細ページを開く
        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        // 4. ページのHTMLを整理して空白を削除
        $html = preg_replace('/\s+/', '', $response->getContent());

        // 5. 休憩開始時間（12:00）と終了時間（13:00）が正しく表示されていることを確認
        $this->assertStringContainsString('<inputtype="time"name="break_start[]"value="12:00">', $html);
        $this->assertStringContainsString('<inputtype="time"name="break_end[]"value="13:00">', $html);
    }
}
