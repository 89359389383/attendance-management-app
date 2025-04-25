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
     *
     * 1. ステータスが出勤中のユーザーにログインする
     * 2. 画面に「休憩入」ボタンが表示されていることを確認する
     * 3. 休憩の処理を行う
     * → 画面上に「休憩入」ボタンが表示され、処理後に画面上のステータスが「休憩中」になる
     */
    public function test_break_start_updates_status_and_ui()
    {
        $user = User::factory()->create()->first();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHour(),
            'status' => '出勤中',
        ]);

        // 画面に「休憩入」ボタンが表示されていることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        // 休憩開始処理
        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);

        // ステータスが「休憩中」になっているか確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => '休憩中',
        ]);

        // 画面上に「ステータス: 休憩中」が表示されていることを確認
        $html = preg_replace('/\s+/', '', $this->actingAs($user)->get('/attendance')->getContent());
        $this->assertStringContainsString('ステータス:休憩中', $html);
    }

    /**
     * ✅ 2. 休憩は一日に何回でもできる
     *
     * 1. ステータスが出勤中であるユーザーにログインする
     * 2. 休憩入と休憩戻の処理を行う
     * 3. 「休憩入」ボタンが表示されることを確認する
     * → 画面上に「休憩入」ボタンが表示される
     */
    public function test_user_can_take_break_multiple_times()
    {
        $user = User::factory()->create()->first();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(2),
            'status' => '出勤中',
        ]);

        // 休憩入 → 休憩戻
        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);
        Attendance::where('id', $attendance->id)->update(['status' => '休憩中']);
        $this->actingAs($user)->post('/attendance', ['action' => 'break_end']);

        // ステータスを出勤中に戻し、「休憩入」ボタンが表示されることを確認
        Attendance::where('id', $attendance->id)->update(['status' => '出勤中']);
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');
    }

    /**
     * ✅ 3. 「休憩戻」ボタンが正しく機能し、ステータスが「出勤中」に戻る
     *
     * 1. ステータスが出勤中であるユーザーにログインする
     * 2. 休憩入の処理を行う
     * 3. 休憩戻の処理を行う
     * → 休憩戻ボタンが表示され、処理後にステータスが「出勤中」に変更される
     */
    public function test_break_end_updates_status_back_to_working()
    {
        $user = User::factory()->create()->first();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHour(),
            'status' => '出勤中',
        ]);

        // 休憩入処理
        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);
        Attendance::where('id', $attendance->id)->update(['status' => '休憩中']);

        // 休憩戻処理
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');

        $this->actingAs($user)->post('/attendance', ['action' => 'break_end']);

        // ステータスが「出勤中」に戻ることを確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => '出勤中',
        ]);
    }

    /**
     * ✅ 4. 休憩戻後に再び「休憩戻」ボタンが表示される（＝休憩何度でもOK）
     *
     * 1. ステータスが出勤中であるユーザーにログインする
     * 2. 休憩入と休憩戻の処理を行い、再度休憩入の処理を行う
     * 3. 「休憩戻」ボタンが表示されることを確認する
     * → 画面上に「休憩戻」ボタンが表示される
     * 4. 勤怠一覧画面から休憩の日付を確認する
     * → 勤怠一覧画面に休憩時刻が正確に記録されている
     */
    public function test_user_can_return_from_break_multiple_times()
    {
        $user = User::factory()->create()->first();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(3),
            'status' => '出勤中',
        ]);

        // 休憩開始 → 休憩終了
        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);
        Attendance::where('id', $attendance->id)->update(['status' => '休憩中']);
        $this->actingAs($user)->post('/attendance', ['action' => 'break_end']);
        Attendance::where('id', $attendance->id)->update(['status' => '出勤中']);

        // もう一度休憩開始
        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);
        Attendance::where('id', $attendance->id)->update(['status' => '休憩中']);

        // 休憩戻ボタンが表示されることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');

        // 勤怠一覧画面で休憩時間が正確に記録されているかを確認
        $response = $this->actingAs($user)->get('/attendance/list');
        $html = preg_replace('/\s+/', '', $response->getContent());
        $this->assertStringContainsString('休憩', $html); // 休憩時刻が表示されていることを確認
    }

    /**
     * ✅ 5. 勤怠一覧に正しく休憩時間が表示される
     *
     * 1. ステータスが勤務中のユーザーにログインする
     * 2. 休憩入と休憩戻の処理を行う
     * 3. 勤怠一覧画面から休憩の日付を確認する
     * → 勤怠一覧画面に休憩時刻が正確に記録されている
     */
    public function test_break_time_displayed_on_attendance_list()
    {
        $user = User::factory()->create()->first();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(5),
            'clock_out' => now(),
            'status' => '退勤済',
        ]);

        // 休憩時間を追加
        $attendance->breakTimes()->create([
            'break_start' => now()->subHours(3),
            'break_end' => now()->subHours(2),
        ]);

        // 勤怠一覧を確認
        $response = $this->actingAs($user)->get('/attendance/list');
        $html = preg_replace('/\s+/', '', $response->getContent());

        // 休憩時間が正しく「1:00」と表示されていることを確認
        $this->assertStringContainsString('1:00', $html); // 1時間の休憩が正しく記録されていること
    }
}
