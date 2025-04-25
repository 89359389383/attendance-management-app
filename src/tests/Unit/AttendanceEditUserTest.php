<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceEditUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 1. 出勤時間が退勤時間より後なら、バリデーションエラーが表示される
     */
    public function test_validation_error_when_clock_in_is_after_clock_out()
    {
        // 1. ユーザー作成とログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. 勤怠情報作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '08:00',
            'clock_out' => '17:00',
            'work_date' => now()->toDateString(),
        ]);

        // 3. 出勤＞退勤となるような入力
        $formData = [
            'clock_in' => '18:00',
            'clock_out' => '17:00',
            'break_start' => [],
            'break_end' => [],
            'note' => 'メモ',
        ];

        // 4. 更新リクエスト送信
        $response = $this->put(route('attendance.update', $attendance->id), $formData);

        // 5. バリデーションエラー確認
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /**
     * ✅ 2. 休憩開始時間が退勤後なら、バリデーションエラーが表示される
     */
    public function test_validation_error_when_break_start_is_after_clock_out()
    {
        // 1. ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. 勤怠作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 3. 休憩開始が退勤後になるように設定
        $formData = [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['18:00'],
            'break_end' => ['19:00'],
            'note' => 'メモ',
        ];

        // 4. 更新リクエスト
        $response = $this->put(route('attendance.update', $attendance->id), $formData);

        // 5. エラー確認
        $response->assertSessionHasErrors(['break_start.0' => '休憩時間が勤務時間外です。']);
    }

    /**
     * ✅ 3. 休憩終了時間が退勤後なら、バリデーションエラーが表示される
     */
    public function test_validation_error_when_break_end_is_after_clock_out()
    {
        // 1. ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. 勤怠作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 3. 休憩終了が退勤後
        $formData = [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['16:00'],
            'break_end' => ['18:00'],
            'note' => 'メモ',
        ];

        // 4. 更新リクエスト
        $response = $this->put(route('attendance.update', $attendance->id), $formData);

        // 5. エラー確認
        $response->assertSessionHasErrors(['break_end.0' => '休憩時間が勤務時間外です。']);
    }

    /**
     * ✅ 4. 備考が未入力の場合、バリデーションエラーが表示される
     */
    public function test_validation_error_when_note_is_missing()
    {
        // 1. ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. 勤怠作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 3. 備考だけ未入力にする
        $formData = [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '',
        ];

        // 4. 更新リクエスト
        $response = $this->put(route('attendance.update', $attendance->id), $formData);

        // 5. エラー確認
        $response->assertSessionHasErrors(['note' => '備考を記入してください。']);
    }

    /**
     * ✅ 5. 修正申請処理が実行される（申請登録 + 管理者画面で確認できる）
     */
    public function test_successful_edit_creates_attendance_request_record()
    {
        // 1. 一般ユーザーを作成・ログイン
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        // 2. 勤怠データ作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 3. フォームデータ（有効な修正内容）
        $formData = [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '修正メモ',
        ];

        // 4. 一般ユーザーが修正申請
        $response = $this->put(route('attendance.update', $attendance->id), $formData);

        // 5. リダイレクトを確認
        $response->assertRedirect(route('attendance.list'));

        // 6. DBに修正申請が登録されていることを確認
        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'note' => '修正メモ',
            'status' => '承認待ち',
        ]);

        // 7. 管理者ユーザーを作成しログインしなおす
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        // 8. 承認一覧ページにアクセスして「承認待ち」のリストが表示されることを確認
        $response = $this->get(route('admin.request.list'));
        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee($user->name);
        $response->assertSee('修正メモ');

        // 9. 承認詳細画面にアクセスし、修正内容が正しく表示されていることを確認
        $requestId = \App\Models\AttendanceRequest::where('attendance_id', $attendance->id)->first()->id;

        $response = $this->get(route('admin.request.show', ['id' => $requestId]));
        $response->assertStatus(200);
        $response->assertSee('修正申請詳細');
        $response->assertSee('09:00');
        $response->assertSee('17:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('修正メモ');
    }

    /**
     * ✅ 6. 「承認待ち」にログインユーザーが行った申請が全て表示されていること
     */
    public function test_user_can_see_all_their_pending_requests()
    {
        // 1. 一般ユーザーでログイン
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        // 2. 勤怠を2件作成
        $attendance1 = Attendance::factory()->create(['user_id' => $user->id]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user->id]);

        // 3. 修正申請を2件作成
        foreach ([$attendance1, $attendance2] as $attendance) {
            $this->put(route('attendance.update', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '17:00',
                'break_start' => ['12:00'],
                'break_end' => ['13:00'],
                'note' => '申請テスト',
            ]);
        }

        // 4. 一般ユーザーの申請一覧ページを確認
        $response = $this->get(route('request.list'));
        $response->assertStatus(200);
        $response->assertSee('承認待ち');

        // 申請が2件あることを確認
        $response->assertSee('申請テスト'); // 申請テストが表示されることを確認

        // 追加の申請を作成
        $attendance3 = Attendance::factory()->create(['user_id' => $user->id]);
        $this->put(route('attendance.update', $attendance3->id), [
            'clock_in' => '10:00',
            'clock_out' => '18:00',
            'break_start' => ['14:00'],
            'break_end' => ['15:00'],
            'note' => '追加申請テスト',
        ]);

        // 追加の申請が表示されることを確認
        $response = $this->get(route('request.list'));
        $response->assertSee('追加申請テスト'); // 追加申請が表示されることを確認
    }

    /**
     * ✅ 7. 「承認済み」に管理者が承認した修正申請が全て表示されている
     */
    public function test_approved_requests_are_visible_to_user()
    {
        // 1. 一般ユーザーと管理者作成
        $user = User::factory()->create(['is_admin' => false]);
        $admin = User::factory()->create(['is_admin' => true]);

        // 2. ログインして勤怠データ作成＆申請
        $this->actingAs($user);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $this->put(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '承認対象メモ',
        ]);

        // 3. 管理者が承認処理を行う
        $requestId = \App\Models\AttendanceRequest::where('user_id', $user->id)->first()->id;
        $this->actingAs($admin);
        $this->post(route('admin.request.approve', ['id' => $requestId]));

        // 4. 一般ユーザーが申請一覧を確認し、「承認済み」が表示されることを確認
        $this->actingAs($user);
        $response = $this->get(route('request.list'));
        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('承認対象メモ');
    }

    /**
     * ✅ 8. 各申請の「詳細」を押下すると申請詳細画面に遷移する
     */
    public function test_clicking_detail_button_navigates_to_detail_screen()
    {
        // 1. 一般ユーザーと管理者作成
        $user = User::factory()->create(['is_admin' => false]);
        $admin = User::factory()->create(['is_admin' => true]);

        // 2. ログイン → 勤怠作成 → 修正申請
        $this->actingAs($user);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $this->put(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '詳細表示用',
        ]);

        // 3. 管理者ログイン → 詳細ページへアクセス
        $requestId = \App\Models\AttendanceRequest::where('user_id', $user->id)->first()->id;
        $this->actingAs($admin);
        $response = $this->get(route('admin.request.show', ['id' => $requestId]));

        // 4. ステータス200、内容表示確認
        $response->assertStatus(200);
        $response->assertSee('修正申請詳細');
        $response->assertSee('詳細表示用');
        $response->assertSee('09:00');
        $response->assertSee('17:00');
    }
}
