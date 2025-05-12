<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;

class EndWorkTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 1. ステータスが出勤中のユーザーに対して「退勤」ボタンが表示され、退勤処理が行える
     */
    public function test_clock_out_button_appears_and_clock_out_process_works()
    {
        // 1. ユーザーと出勤中の勤怠データを作成（clock_out は明示的に null にする）
        $user = User::factory()->create()->first();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => null,
            'status' => '出勤中',
        ]);

        // 2. 勤怠画面にアクセスして「退勤」ボタンが見えることを確認
        $response = $this->actingAs($user)->get(route('attendance.show'));
        $response->assertStatus(200);
        $response->assertSee('退勤'); // Blade側で表示されるボタン文言

        // 3. 退勤処理を実行
        $response = $this->actingAs($user)->post(route('attendance.store'), [
            'action' => 'clock_out',
        ]);

        // 4. 成功時のリダイレクトとセッション確認
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // 5. 再度画面にアクセスして、「退勤済」メッセージが表示されていることを確認
        $response = $this->actingAs($user)->get(route('attendance.show'));
        $response->assertSee('退勤済');
    }

    /**
     * ✅ 2. 勤怠の退勤時刻が管理画面（勤怠一覧）で正しく表示される
     */
    public function test_clock_out_time_appears_in_admin_attendance_list()
    {
        // 1. 管理者と一般ユーザーを作成->first()
        $admin = User::factory()->create(['is_admin' => true])->first();
        $user = User::factory()->create()->first();

        // 2. 勤務外ステータスで勤怠データを初期作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'status' => '勤務外',
        ]);

        // 3. 出勤処理（POST）
        $this->actingAs($user)->post(route('attendance.store'), [
            'action' => 'clock_in',
        ]);

        // 4. 退勤処理（POST）
        $this->actingAs($user)->post(route('attendance.store'), [
            'action' => 'clock_out',
        ]);

        // 5. 管理者として勤怠一覧画面にアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list'));

        // 6. レスポンスステータスとHTMLを取得
        $response->assertStatus(200);
        $html = preg_replace('/\s+/', '', $response->getContent());

        // 7. 退勤時刻がHTMLに含まれているかをチェック
        $updatedAttendance = Attendance::first();
        $expectedClockOut = $updatedAttendance->clock_out->format('H:i');
        $expected = '<td>' . $expectedClockOut . '</td>';
        $expected = preg_replace('/\s+/', '', $expected);

        $this->assertStringContainsString($expected, $html);
    }
}
