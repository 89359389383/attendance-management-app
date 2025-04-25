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
        $today = Carbon::now()->format('Y年n月j日(D)'); // 例: 2025年4月23日(Wed)
        $time = Carbon::now()->format('H:i');           // 例: 21:55

        // 3. ログインして勤怠打刻画面へアクセス
        $response = $this->actingAs($user)->get(route('attendance.show'));

        // 4. 日付と時間が画面に表示されていることを確認（個別にチェック）
        $response->assertSee($today);
        $response->assertSee($time);
    }
}
