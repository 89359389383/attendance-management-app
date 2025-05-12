<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 1. 「休憩入」ボタンが正しく機能し、ステータスが「休憩中」になる
     */
    public function test_break_start_updates_status_and_ui()
    {
        // 1. ユーザーを作成し、ログインする
        $user = User::factory()->create()->first();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHour(),
            'status' => '出勤中',
        ]);

        // 2. 画面に「休憩入」ボタンが表示されていることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        // 3. 休憩開始処理を行う
        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);

        // 4. ステータスが「休憩中」になっているか確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => '休憩中',
        ]);

        // 5. 画面上に「ステータス: 休憩中」が表示されていることを確認
        $html = preg_replace('/\s+/', '', $this->actingAs($user)->get('/attendance')->getContent());
        $this->assertStringContainsString('休憩中', $html);
    }

    /**
     * ✅ 2. 休憩は一日に何回でもできる
     */
    public function test_user_can_take_break_multiple_times()
    {
        // 1. ユーザーを作成し、ログインする
        $user = User::factory()->create()->first();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(2),
            'status' => '出勤中',
        ]);
        $this->actingAs($user);

        // 2. 休憩入 → 休憩戻
        $this->post('/attendance', ['action' => 'break_start']);
        Attendance::where('id', $attendance->id)->update(['status' => '休憩中']);
        $this->post('/attendance', ['action' => 'break_end']);

        // 3. ステータスを出勤中に戻し、「休憩入」ボタンが表示されることを確認
        Attendance::where('id', $attendance->id)->update(['status' => '出勤中']);
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');
    }

    /**
     * ✅ 3. 休憩戻ボタンが正しく機能する
     */
    public function test_break_end_button_functions_correctly()
    {
        // 1. ユーザーを作成し、ログインする
        $user = User::factory()->create()->first();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHour(),
            'status' => '出勤中',
        ]);
        $this->actingAs($user);

        // 2. 休憩入処理を実行
        $this->post('/attendance', ['action' => 'break_start']);
        $attendance->refresh();
        $this->assertEquals('休憩中', $attendance->fresh()->status);

        // 3. 休憩戻処理を実行
        $this->post('/attendance', ['action' => 'break_end']);
        $attendance->refresh();
        $this->assertEquals('出勤中', $attendance->status);

        // 4. 「休憩戻」ボタンが表示される
        $this->post('/attendance', ['action' => 'break_start']);
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /**
     * ✅ 4. 休憩戻は一日に何回でもできる
     */
    public function test_break_end_updates_status_back_to_working()
    {
        // 1. ユーザーを作成し、ログインする
        $user = User::factory()->create()->first();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHour(),
            'status' => '出勤中',
        ]);

        $this->actingAs($user);

        // 2. 休憩入 → 休憩戻 → 再び休憩入
        $this->post('/attendance', ['action' => 'break_start']);
        Attendance::where('id', $attendance->id)->update(['status' => '休憩中']);

        $this->post('/attendance', ['action' => 'break_end']);
        Attendance::where('id', $attendance->id)->update(['status' => '出勤中']);

        $this->post('/attendance', ['action' => 'break_start']);
        Attendance::where('id', $attendance->id)->update(['status' => '休憩中']);

        // 3. 「休憩戻」ボタンが表示されることを確認する
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /**
     * ✅ 5. 勤怠一覧に正しく休憩時間が表示される
     */
    public function test_break_time_displayed_on_attendance_list()
    {
        // 1. ユーザーを作成し、ログインする
        $user = User::factory()->create()->first();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHour(),
            'status' => '出勤中',
        ]);

        // 2. 休憩時間を追加
        $attendance->breakTimes()->create([
            'break_start' => now()->subHours(3),
            'break_end' => now()->subHours(2),
        ]);

        // 3. 勤怠一覧を確認
        $response = $this->actingAs($user)->get('/attendance/list');
        $html = preg_replace('/\s+/', '', $response->getContent());

        // 4. 休憩時間が正しく「1:00」と表示されていることを確認
        $this->assertStringContainsString('1:00', $html); // 1時間の休憩が正しく記録されていること
    }
}
