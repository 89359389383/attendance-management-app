<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class StartWorkTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 1. ステータスが勤務外のユーザーに出勤ボタンが表示される
     */
    public function test_attendance_button_is_displayed_when_status_is_out()
    {
        // 1. ステータスが勤務外のユーザーを作成
        $user = User::factory()->create()->first();
        $this->actingAs($user);

        // 2. 出勤データがない状態で出勤ページを表示
        $response = $this->get(route('attendance.show'));

        // 3. 出勤ボタンが表示されていることを確認
        $response->assertSee('出勤');
    }

    /**
     * ✅ 2. 出勤処理が正常に行われる
     */
    public function test_user_can_clock_in()
    {
        // 1. 勤務外ステータスのユーザーを作成
        $user = User::factory()->create()->first();
        $this->actingAs($user);

        // 2. 出勤ボタンを押す（POSTリクエスト）
        $response = $this->post(route('attendance.store'), [
            'action' => 'clock_in',
        ]);

        // 3. 処理後に「出勤しました」のフラッシュメッセージが表示される
        $response->assertRedirect();
        $response->assertSessionHas('success', '出勤しました。');

        // 4. 出勤記録がDBに保存されているか確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => '出勤中',
        ]);
    }

    /**
     * ✅ 3. 出勤は一日一回しかできない（ステータスが退勤済の場合は出勤ボタン非表示）
     */
    public function test_user_cannot_clock_in_twice()
    {
        // 1. 出勤済（退勤済）のデータを持つユーザーを作成
        $user = User::factory()->create()->first();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => '退勤済',
        ]);
        $this->actingAs($user);

        // 2. 出勤ページを開く
        $response = $this->get(route('attendance.show'));

        // 3. 出勤ボタンが表示されないことを確認
        $response->assertDontSee('出勤');
    }

    /**
     * ✅ 4. 出勤時刻が管理画面に正しく反映される
     */
    public function test_clock_in_time_appears_on_admin_list()
    {
        // 1. ユーザーを作成し、出勤処理を実行
        $user = User::factory()->create()->first();
        $this->actingAs($user)->post(route('attendance.store'), [
            'action' => 'clock_in',
        ]);

        // 2. 管理者ユーザーとして再ログイン（adminミドルウェアをスキップする場合は適宜追加）
        $admin = User::factory()->create(['is_admin' => true])->first();
        $this->actingAs($admin);

        // 3. 管理画面の勤怠一覧ページにアクセス
        $response = $this->get(route('admin.attendance.list'));

        // 4. 現在の日付と出勤時刻が含まれているか確認（出勤したばかりなので存在するはず）
        $today = Carbon::now()->format('Y年m月d日'); // 年月日を「2025年04月24日」の形式に変更
        $clockInTime = Carbon::now()->format('H:i');

        $html = preg_replace('/\s+/', '', $response->getContent());
        $this->assertStringContainsString($today, $html);  // 変更された日付フォーマットに合わせる
        $this->assertStringContainsString($clockInTime, $html);
    }
}
