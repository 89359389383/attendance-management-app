<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceDetailAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 1. 勤怠詳細画面に表示される内容が選択した勤怠情報と一致する
     */
    public function test_attendance_detail_displays_correct_data()
    {
        $admin = User::factory()->create(['is_admin' => true])->first();
        $this->actingAs($admin, 'admin'); // 【修正】adminガードでログイン

        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'note' => '詳細確認用メモ',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => '13:00',
        ]);

        $response = $this->get(route('admin.attendance.detail', $attendance->id));
        $response->assertStatus(200);

        $html = preg_replace('/\s+/', '', $response->getContent());
        $this->assertStringContainsString('09:00', $html);
        $this->assertStringContainsString('17:00', $html);
        $this->assertStringContainsString('12:00', $html);
        $this->assertStringContainsString('13:00', $html);
        $this->assertStringContainsString('詳細確認用メモ', $html);
    }

    /**
     * ✅ 2. 出勤時間が退勤時間より後なら、バリデーションエラーが表示される
     */
    public function test_validation_fails_if_clock_in_is_after_clock_out()
    {
        $admin = User::factory()->create(['is_admin' => true])->first();
        $this->actingAs($admin, 'admin'); // 【修正】adminガードでログイン

        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $formData = [
            'clock_in' => '18:00',
            'clock_out' => '17:00',
            'break_start' => [],
            'break_end' => [],
            'note' => '正常な備考',
        ];

        $response = $this->put(route('admin.attendance.update', $attendance->id), $formData);
        $response->assertSessionHasErrors(['clock_in' => '出勤時間もしくは退勤時間が不適切な値です。']);
    }

    /**
     * ✅ 3. 休憩開始時間が退勤時間より後なら、バリデーションエラーが表示される
     */
    public function test_validation_fails_if_break_start_is_after_clock_out()
    {
        $admin = User::factory()->create(['is_admin' => true])->first();
        $this->actingAs($admin, 'admin'); // 【修正】adminガードでログイン

        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $formData = [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['18:00'],
            'break_end' => ['19:00'],
            'note' => '正常な備考',
        ];

        $response = $this->put(route('admin.attendance.update', $attendance->id), $formData);
        $response->assertSessionHasErrors(['break_start.0' => '休憩時間が勤務時間外です。']);
    }

    /**
     * ✅ 4. 休憩終了時間が退勤時間より後なら、バリデーションエラーが表示される
     */
    public function test_validation_fails_if_break_end_is_after_clock_out()
    {
        $admin = User::factory()->create(['is_admin' => true])->first();
        $this->actingAs($admin, 'admin'); // 【修正】adminガードでログイン

        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $formData = [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['12:00'],
            'break_end' => ['18:00'],
            'note' => '正常な備考',
        ];

        $response = $this->put(route('admin.attendance.update', $attendance->id), $formData);
        $response->assertSessionHasErrors(['break_end.0' => '休憩時間が勤務時間外です。']);
    }

    /**
     * ✅ 5. 備考欄が未入力の場合、バリデーションエラーが表示される
     */
    public function test_validation_fails_if_note_is_empty()
    {
        $admin = User::factory()->create(['is_admin' => true])->first();
        $this->actingAs($admin, 'admin'); // 【修正】adminガードでログイン

        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $formData = [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '', // 備考空欄
        ];

        $response = $this->put(route('admin.attendance.update', $attendance->id), $formData);
        $response->assertSessionHasErrors(['note' => '備考を記入してください。']);
    }
}
