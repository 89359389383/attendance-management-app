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
        // 1. 管理者ユーザーでログインする
        $admin = User::factory()->create(['is_admin' => true])->first(); // 管理者ユーザーを作成
        $this->actingAs($admin, 'admin'); // adminガードでログイン

        // 2. 勤怠情報を作成
        $user = User::factory()->create(); // ユーザーを作成
        $attendance = Attendance::factory()->create([ // 勤怠情報を作成
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'note' => '詳細確認用メモ', // メモ
        ]);

        // 3. 休憩情報を作成
        BreakTime::factory()->create([ // 休憩情報を作成
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => '13:00',
        ]);

        // 4. 勤怠詳細画面を表示
        $response = $this->get(route('admin.attendance.detail', $attendance->id)); // 詳細画面にアクセス
        $response->assertStatus(200); // ステータスコード200（OK）を確認

        // 5. 表示内容が正しいか確認
        $html = preg_replace('/\s+/', '', $response->getContent()); // 空白を除去したHTMLを取得
        $this->assertStringContainsString('09:00', $html); // 出勤時間が表示されていることを確認
        $this->assertStringContainsString('17:00', $html); // 退勤時間が表示されていることを確認
        $this->assertStringContainsString('12:00', $html); // 休憩開始時間が表示されていることを確認
        $this->assertStringContainsString('13:00', $html); // 休憩終了時間が表示されていることを確認
        $this->assertStringContainsString('詳細確認用メモ', $html); // メモが表示されていることを確認
    }

    /**
     * ✅ 2. 出勤時間が退勤時間より後なら、バリデーションエラーが表示される
     */
    public function test_validation_fails_if_clock_in_is_after_clock_out()
    {
        // 1. 管理者ユーザーでログイン
        $admin = User::factory()->create(['is_admin' => true])->first(); // 管理者ユーザーを作成
        $this->actingAs($admin, 'admin'); // adminガードでログイン

        // 2. 勤怠情報を作成
        $user = User::factory()->create(); // ユーザーを作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]); // 勤怠情報を作成

        // 3. 出勤時間が退勤時間より後の場合のデータを準備
        $formData = [
            'clock_in' => '18:00', // 出勤時間
            'clock_out' => '17:00', // 退勤時間
            'break_start' => [],
            'break_end' => [],
            'note' => '正常な備考',
        ];

        // 4. 保存処理を実行し、バリデーションエラーを確認
        $response = $this->put(route('admin.attendance.update', $attendance->id), $formData); // 更新処理
        $response->assertSessionHasErrors(['clock_in' => '出勤時間もしくは退勤時間が不適切な値です。']); // エラーメッセージが表示されることを確認
    }

    /**
     * ✅ 3. 休憩開始時間が退勤時間より後なら、バリデーションエラーが表示される
     */
    public function test_validation_fails_if_break_start_is_after_clock_out()
    {
        // 1. 管理者ユーザーでログイン
        $admin = User::factory()->create(['is_admin' => true])->first(); // 管理者ユーザーを作成
        $this->actingAs($admin, 'admin'); // adminガードでログイン

        // 2. 勤怠情報を作成
        $user = User::factory()->create(); // ユーザーを作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]); // 勤怠情報を作成

        // 3. 休憩開始時間が退勤時間より後の場合のデータを準備
        $formData = [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['18:00'], // 休憩開始時間
            'break_end' => ['19:00'], // 休憩終了時間
            'note' => '正常な備考',
        ];

        // 4. 保存処理を実行し、バリデーションエラーを確認
        $response = $this->put(route('admin.attendance.update', $attendance->id), $formData); // 更新処理
        $response->assertSessionHasErrors(['break_start.0' => '休憩時間が勤務時間外です。']); // エラーメッセージが表示されることを確認
    }

    /**
     * ✅ 4. 休憩終了時間が退勤時間より後なら、バリデーションエラーが表示される
     */
    public function test_validation_fails_if_break_end_is_after_clock_out()
    {
        // 1. 管理者ユーザーでログイン
        $admin = User::factory()->create(['is_admin' => true])->first(); // 管理者ユーザーを作成
        $this->actingAs($admin, 'admin'); // adminガードでログイン

        // 2. 勤怠情報を作成
        $user = User::factory()->create(); // ユーザーを作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]); // 勤怠情報を作成

        // 3. 休憩終了時間が退勤時間より後の場合のデータを準備
        $formData = [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['12:00'],
            'break_end' => ['18:00'], // 休憩終了時間
            'note' => '正常な備考',
        ];

        // 4. 保存処理を実行し、バリデーションエラーを確認
        $response = $this->put(route('admin.attendance.update', $attendance->id), $formData); // 更新処理
        $response->assertSessionHasErrors(['break_end.0' => '休憩時間が勤務時間外です。']); // エラーメッセージが表示されることを確認
    }

    /**
     * ✅ 5. 備考欄が未入力の場合、バリデーションエラーが表示される
     */
    public function test_validation_fails_if_note_is_empty()
    {
        // 1. 管理者ユーザーでログイン
        $admin = User::factory()->create(['is_admin' => true])->first(); // 管理者ユーザーを作成
        $this->actingAs($admin, 'admin'); // adminガードでログイン

        // 2. 勤怠情報を作成
        $user = User::factory()->create(); // ユーザーを作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]); // 勤怠情報を作成

        // 3. 備考欄を未入力のままデータを準備
        $formData = [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '', // 備考空欄
        ];

        // 4. 保存処理を実行し、バリデーションエラーを確認
        $response = $this->put(route('admin.attendance.update', $attendance->id), $formData); // 更新処理
        $response->assertSessionHasErrors(['note' => '備考を記入してください。']); // エラーメッセージが表示されることを確認
    }
}
