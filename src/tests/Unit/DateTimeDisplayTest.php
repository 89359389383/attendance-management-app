<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class DateTimeDisplayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 勤怠打刻画面に現在の日付と時刻が表示されていることを確認するテスト
     */
    public function test_attendance_screen_displays_current_date_and_time()
    {
        // 1. テストユーザーを作成
        $user = User::factory()->create()->first();

        // 2. 現在日時を取得（ビューのフォーマットに合わせる）
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $today = Carbon::now();
        $formattedDate = $today->format('Y年n月j日') . '(' . $weekdays[$today->dayOfWeek] . ')'; // 日本語の曜日を表示
        $time = $today->format('H:i'); // 時刻

        // 3. ログインして勤怠打刻画面へアクセス
        $response = $this->actingAs($user)->get(route('attendance.show'));

        // 4. 日付と時間が画面に表示されていることを確認（個別にチェック）
        $response->assertSee($formattedDate);
        $response->assertSee($time);
    }
}
