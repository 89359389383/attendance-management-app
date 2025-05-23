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
     * ✅ 1. ステータスが勤務外のユーザーに出勤ボタンが表示され、出勤処理が正常に行われる
     */
    public function test_attendance_button_and_clock_in_process()
    {
        // 1. ステータスが勤務外のユーザーを作成
        $user = User::factory()->create()->first();
        $this->actingAs($user);

        // 2. 出勤データがない状態で出勤ページを表示
        $response = $this->get(route('attendance.show'));

        // 3. 出勤ボタンが表示されていることを確認
        $response->assertSee('出勤');

        // 4. 出勤ボタンを押す（POSTリクエスト）
        $response = $this->post(route('attendance.store'), [
            'action' => 'clock_in',
        ]);

        // 5. 出勤記録がDBに保存されているか確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => '出勤中',
        ]);
    }

    /**
     * ✅ 2. 出勤は一日一回しかできない（ステータスが退勤済の場合は出勤ボタン非表示）
     */
    public function test_user_cannot_clock_in_twice()
    {
        // 1. 出勤済（退勤済）のデータを持つユーザーを作成
        $user = User::factory()->create()->first();
        Attendance::factory()->create([
            'user_id' => $user->id, // ✅ ちゃんと存在するuser_idを指定
            'work_date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'note' => 'テスト出勤',
            'status' => '退勤済',
        ]);
        $this->actingAs($user);

        // 2. 出勤ページを開く
        $response = $this->get(route('attendance.show'));

        // 3. 出勤ボタンが表示されないことを確認
        $response->assertDontSee('出勤');
    }

    /**
     * ✅ 3. 出勤時刻が管理画面に正しく反映される
     */
    public function test_clock_in_time_appears_on_admin_list()
    {
        // 1. ユーザーを作成し、出勤処理を実行
        $user = User::factory()->create()->first();
        $this->actingAs($user)->post(route('attendance.store'), [
            'action' => 'clock_in',
        ]);

        // 2. 管理者ユーザーを作成
        $admin = User::factory()->create([
            'is_admin' => true,
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 3. 管理者ログイン（POSTで本物のログインを通す）
        $response = $this->post(route('admin.login'), [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect('/admin/attendance/list');

        // 4. 管理画面の勤怠一覧ページにアクセス
        $response = $this->get(route('admin.attendance.list'));

        // 5. 現在の日付と出勤時刻が含まれているか確認
        $today = Carbon::now()->format('Y年m月d日');
        $clockInTime = Carbon::now()->format('H:i');

        $html = preg_replace('/\s+/', '', $response->getContent());
        $this->assertStringContainsString($today, $html);
        $this->assertStringContainsString($clockInTime, $html);
    }
}
