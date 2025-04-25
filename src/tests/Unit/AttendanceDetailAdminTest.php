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
        // 1. 管理者ユーザー作成＆ログイン
        $admin = User::factory()->create(['is_admin' => true]); // 管理者ユーザーを作成
        $this->actingAs($admin); // 管理者ユーザーとしてログイン

        // 2. 勤怠情報を持つ一般ユーザーを作成
        $user = User::factory()->create(); // 一般ユーザーを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id, // 作成したユーザーのIDを関連付ける
            'clock_in' => '09:00', // 出勤時間
            'clock_out' => '17:00', // 退勤時間
            'note' => '詳細確認用メモ', // 備考
        ]);

        // 3. 勤怠情報に休憩データを関連付ける
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id, // 勤怠情報のIDを関連付ける
            'break_start' => '12:00', // 休憩開始時間
            'break_end' => '13:00', // 休憩終了時間
        ]);

        // 4. 管理者が勤怠詳細ページへアクセス
        $response = $this->get(route('admin.attendance.detail', $attendance->id)); // 勤怠詳細ページをリクエスト
        $response->assertStatus(200); // 正常にページが表示されることを確認

        // 5. HTMLを取得して検証
        $html = preg_replace('/\s+/', '', $response->getContent()); // 不要な空白を削除してHTMLを整形
        $this->assertStringContainsString('09:00', $html); // 出勤時間が表示されているか確認
        $this->assertStringContainsString('17:00', $html); // 退勤時間が表示されているか確認
        $this->assertStringContainsString('12:00', $html); // 休憩開始時間が表示されているか確認
        $this->assertStringContainsString('13:00', $html); // 休憩終了時間が表示されているか確認
        $this->assertStringContainsString('詳細確認用メモ', $html); // 備考が表示されているか確認
    }

    /**
     * ✅ 2. 出勤時間が退勤時間より後なら、バリデーションエラーが表示される
     */
    public function test_validation_fails_if_clock_in_is_after_clock_out()
    {
        // 1. 管理者ログイン
        $admin = User::factory()->create(['is_admin' => true]); // 管理者ユーザーを作成
        $this->actingAs($admin); // 管理者としてログイン

        // 2. 勤怠作成
        $user = User::factory()->create(); // 一般ユーザーを作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]); // 作成したユーザーのIDを関連付ける

        // 3. clock_in > clock_out のデータ送信
        $formData = [
            'clock_in' => '18:00', // 出勤時間（退勤時間より後に設定）
            'clock_out' => '17:00', // 退勤時間
            'break_start' => [], // 休憩開始時間
            'break_end' => [], // 休憩終了時間
            'note' => '正常な備考', // 備考
        ];

        // 4. 更新リクエスト
        $response = $this->put(route('admin.attendance.update', $attendance->id), $formData); // 勤怠情報を更新
        $response->assertSessionHasErrors(['clock_in' => '出勤時間もしくは退勤時間が不適切な値です。']); // バリデーションエラーの確認
    }

    /**
     * ✅ 3. 休憩開始時間が退勤時間より後なら、バリデーションエラーが表示される
     */
    public function test_validation_fails_if_break_start_is_after_clock_out()
    {
        // 1. 管理者ログイン
        $admin = User::factory()->create(['is_admin' => true]); // 管理者ユーザーを作成
        $this->actingAs($admin); // 管理者としてログイン

        // 2. 勤怠作成
        $user = User::factory()->create(); // 一般ユーザーを作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]); // 作成したユーザーのIDを関連付ける

        // 3. 休憩開始が退勤後
        $formData = [
            'clock_in' => '09:00', // 出勤時間
            'clock_out' => '17:00', // 退勤時間
            'break_start' => ['18:00'], // 休憩開始時間（退勤時間より後）
            'break_end' => ['19:00'], // 休憩終了時間
            'note' => '正常な備考', // 備考
        ];

        // 4. 更新リクエスト
        $response = $this->put(route('admin.attendance.update', $attendance->id), $formData); // 勤怠情報を更新
        $response->assertSessionHasErrors(['break_start.0' => '休憩時間が勤務時間外です。']); // バリデーションエラーの確認
    }

    /**
     * ✅ 4. 休憩終了時間が退勤時間より後なら、バリデーションエラーが表示される
     */
    public function test_validation_fails_if_break_end_is_after_clock_out()
    {
        // 1. 管理者ログイン
        $admin = User::factory()->create(['is_admin' => true]); // 管理者ユーザーを作成
        $this->actingAs($admin); // 管理者としてログイン

        // 2. 勤怠作成
        $user = User::factory()->create(); // 一般ユーザーを作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]); // 作成したユーザーのIDを関連付ける

        // 3. 休憩終了が退勤後
        $formData = [
            'clock_in' => '09:00', // 出勤時間
            'clock_out' => '17:00', // 退勤時間
            'break_start' => ['12:00'], // 休憩開始時間
            'break_end' => ['18:00'], // 休憩終了時間（退勤時間より後）
            'note' => '正常な備考', // 備考
        ];

        // 4. 更新リクエスト
        $response = $this->put(route('admin.attendance.update', $attendance->id), $formData); // 勤怠情報を更新
        $response->assertSessionHasErrors(['break_end.0' => '休憩時間が勤務時間外です。']); // バリデーションエラーの確認
    }

    /**
     * ✅ 5. 備考欄が未入力の場合、バリデーションエラーが表示される
     */
    public function test_validation_fails_if_note_is_empty()
    {
        // 1. 管理者ログイン
        $admin = User::factory()->create(['is_admin' => true]); // 管理者ユーザーを作成
        $this->actingAs($admin); // 管理者としてログイン

        // 2. 勤怠作成
        $user = User::factory()->create(); // 一般ユーザーを作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]); // 作成したユーザーのIDを関連付ける

        // 3. 備考を未入力で送信
        $formData = [
            'clock_in' => '09:00', // 出勤時間
            'clock_out' => '17:00', // 退勤時間
            'break_start' => ['12:00'], // 休憩開始時間
            'break_end' => ['13:00'], // 休憩終了時間
            'note' => '', // 備考（空欄）
        ];

        // 4. 更新リクエスト
        $response = $this->put(route('admin.attendance.update', $attendance->id), $formData); // 勤怠情報を更新
        $response->assertSessionHasErrors(['note' => '備考を記入してください。']); // バリデーションエラーの確認
    }
}
