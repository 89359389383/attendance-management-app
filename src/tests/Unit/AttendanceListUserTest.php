<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 1. 自分の勤怠情報が全て表示されていることを確認
     */
    public function test_user_can_see_their_own_attendance_records()
    {
        // 1. ユーザーを作成
        $user = User::factory()->create()->first();

        // 2. ユーザーに対して、今月の1〜3日の勤怠情報を明示的に3件作成
        foreach ([1, 2, 3] as $day) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => now()->startOfMonth()->addDays($day - 1)->format('Y-m-d'),
            ]);
        }

        // 3. ログイン状態で勤怠一覧ページにアクセス
        $response = $this->actingAs($user)->get(route('attendance.list'));

        // 4. 勤怠情報3件が表示されていることを確認
        $response->assertStatus(200);
        $this->assertEquals(3, substr_count($response->getContent(), 'class="detail-link"'));
    }

    /**
     * ✅ 2. 勤怠一覧画面に現在の月が表示されることを確認
     */
    public function test_current_month_is_displayed_on_attendance_list()
    {
        // 1. ユーザーを作成
        $user = User::factory()->create()->first();

        // 2. 勤怠一覧ページにアクセス
        $response = $this->actingAs($user)->get(route('attendance.list'));

        // 3. ステータスコードが200であることを確認
        $response->assertStatus(200);

        // 4. 現在の月を取得
        $currentMonth = Carbon::now()->format('Y年m月');

        // 5. 現在の月が表示されていることを確認
        $response->assertSee($currentMonth);
    }

    /**
     * ✅ 3. 「前月」ボタンを押した時に前月の情報が表示されることを確認
     */
    public function test_previous_month_attendance_is_displayed()
    {
        // 1. ユーザーを作成
        $user = User::factory()->create()->first();

        // 2. 前月の日付を取得
        $previousMonth = Carbon::now()->subMonth()->format('Y-m');

        // 3. 前月の勤怠情報を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d')
        ]);

        // 4. 勤怠一覧ページにアクセス
        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => $previousMonth]));

        // 5. ステータスコードが200であることを確認
        $response->assertStatus(200);

        // 6. 前月の勤怠情報が表示されていることを確認
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $workDate = Carbon::parse($attendance->work_date);
        $formattedDate = $workDate->format('m/d') . '(' . $weekdays[$workDate->dayOfWeek] . ')'; // 日本語曜日
        $response->assertSee($formattedDate);
    }

    /**
     * ✅ 4. 「翌月」ボタンを押した時に翌月の情報が表示されることを確認
     */
    public function test_next_month_attendance_is_displayed()
    {
        // 1. ユーザーを作成
        $user = User::factory()->create()->first();

        // 2. 翌月の日付を取得
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');

        // 3. 翌月の勤怠情報を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d')
        ]);

        // 4. 勤怠一覧ページにアクセス
        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => $nextMonth]));

        // 5. ステータスコードが200であることを確認
        $response->assertStatus(200);

        // 6. 翌月の勤怠情報が表示されていることを確認
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $workDate = Carbon::parse($attendance->work_date);
        $formattedDate = $workDate->format('m/d') . '(' . $weekdays[$workDate->dayOfWeek] . ')'; // 日本語曜日
        $response->assertSee($formattedDate);
    }

    /**
     * ✅ 5. 詳細ボタンを押すと勤怠詳細画面に遷移することを確認
     */
    public function test_clicking_detail_button_redirects_to_detail_page()
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = User::factory()->create()->first();

        // 今月の日付（例：2025年5月10日）で勤怠データ作成
        $workDate = Carbon::create(2025, 5, 10);
        Carbon::setTestNow($workDate); // 月フィルタに確実に一致させる
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $workDate->format('Y-m-d'),
        ]);

        $this->actingAs($user);

        // 2. 勤怠一覧ページを開く
        $response = $this->get('/attendance/list?month=' . $workDate->format('Y-m')); // 月指定も明示的に
        $response->assertStatus(200);
        $html = $response->getContent();

        // 3. 詳細リンクが含まれていることを確認
        $this->assertStringContainsString("/attendance/{$attendance->id}", $html);

        // 4. 詳細ページに遷移し、ステータスと表示確認
        $detailResponse = $this->get("/attendance/{$attendance->id}");
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('勤怠詳細');

        Carbon::setTestNow(); // テスト時間のリセット
    }
}
