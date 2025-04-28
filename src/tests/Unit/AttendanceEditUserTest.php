<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceEditUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 出勤時間が退勤時間より後の場合にバリデーションエラーを確認
     */
    public function test_validation_error_when_clock_in_is_after_clock_out()
    {
        // 1. ユーザーを作成
        $user = User::factory()->create(); // 【修正】first()を削除
        $this->actingAs($user);

        // 2. 勤怠情報を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '08:00',
            'clock_out' => '17:00',
            'work_date' => now()->toDateString(),
        ]);

        // 3. 出勤時間が退勤時間より後の場合のフォームデータ
        $formData = [
            'clock_in' => '18:00',
            'clock_out' => '17:00',
            'break_start' => [],
            'break_end' => [],
            'note' => 'メモ',
        ];

        // 4. エラーが返ることを確認
        $response = $this->put(route('attendance.update', $attendance->id), $formData);
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /**
     * ✅ 休憩開始時間が退勤時間より後の場合にバリデーションエラーを確認
     */
    public function test_validation_error_when_break_start_is_after_clock_out()
    {
        // 1. ユーザーを作成
        $user = User::factory()->create(); // 【修正】first()を削除
        $this->actingAs($user);

        // 2. 勤怠情報を作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 3. 休憩開始時間が退勤時間より後の場合のフォームデータ
        $formData = [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['18:00'],
            'break_end' => ['19:00'],
            'note' => 'メモ',
        ];

        // 4. エラーが返ることを確認
        $response = $this->put(route('attendance.update', $attendance->id), $formData);
        $response->assertSessionHasErrors(['break_start.0' => '休憩時間が勤務時間外です。']);
    }

    /**
     * ✅ 休憩終了時間が退勤時間より後の場合にバリデーションエラーを確認
     */
    public function test_validation_error_when_break_end_is_after_clock_out()
    {
        // 1. ユーザーを作成
        $user = User::factory()->create(); // 【修正】first()を削除
        $this->actingAs($user);

        // 2. 勤怠情報を作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 3. 休憩終了時間が退勤時間より後の場合のフォームデータ
        $formData = [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['16:00'],
            'break_end' => ['18:00'],
            'note' => 'メモ',
        ];

        // 4. エラーが返ることを確認
        $response = $this->put(route('attendance.update', $attendance->id), $formData);
        $response->assertSessionHasErrors(['break_end.0' => '休憩時間が勤務時間外です。']);
    }

    /**
     * ✅ 備考欄が空白の場合にバリデーションエラーを確認
     */
    public function test_validation_error_when_note_is_missing()
    {
        // 1. ユーザーを作成
        $user = User::factory()->create(); // 【修正】first()を削除
        $this->actingAs($user);

        // 2. 勤怠情報を作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 3. 備考欄が空の場合のフォームデータ
        $formData = [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '',
        ];

        // 4. エラーが返ることを確認
        $response = $this->put(route('attendance.update', $attendance->id), $formData);
        $response->assertSessionHasErrors(['note' => '備考を記入してください。']);
    }

    /**
     * ✅ 勤怠情報の修正後、申請レコードが作成されることを確認
     */
    public function test_successful_edit_creates_attendance_request_record()
    {
        // 1. ユーザーを作成（管理者でないユーザー）
        $user = User::factory()->create(['is_admin' => false]); // 【修正】first()削除
        $this->actingAs($user);

        // 2. 勤怠情報を作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 3. フォームデータを準備
        $formData = [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '修正メモ',
        ];

        // 4. 修正後にリダイレクトされることを確認
        $response = $this->put(route('attendance.update', $attendance->id), $formData);
        $response->assertRedirect(route('attendance.list'));

        // 5. 申請レコードが作成されていることを確認
        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'note' => '修正メモ',
            'status' => '承認待ち',
        ]);

        // 6. 管理者としてログインし、申請一覧を確認
        $admin = User::factory()->create(['is_admin' => true]); // 【修正】first()削除
        $this->actingAs($admin, 'admin'); // 【修正】adminガードを指定

        $response = $this->get(route('admin.request.list'));
        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee($user->name);
        $response->assertSee('修正メモ');

        // 7. 詳細画面に遷移し、正しい情報が表示されていることを確認
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
     * ✅ ユーザーが自分の承認待ち申請をすべて確認できることを確認
     */
    public function test_user_can_see_all_their_pending_requests()
    {
        // 1. ユーザーを作成（管理者でないユーザー）
        $user = User::factory()->create(['is_admin' => false]); // 【修正】first()削除
        $this->actingAs($user);

        // 2. 2つの勤怠情報を作成し、申請を行う
        $attendance1 = Attendance::factory()->create(['user_id' => $user->id]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user->id]);

        foreach ([$attendance1, $attendance2] as $attendance) {
            $this->put(route('attendance.update', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '17:00',
                'break_start' => ['12:00'],
                'break_end' => ['13:00'],
                'note' => '申請テスト',
            ]);
        }

        // 3. ユーザーが自分の申請一覧を確認できることを確認
        $response = $this->get(route('request.list'));
        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('申請テスト');

        // 4. 新たに申請を追加し、その内容も確認できることを確認
        $attendance3 = Attendance::factory()->create(['user_id' => $user->id]);
        $this->put(route('attendance.update', $attendance3->id), [
            'clock_in' => '10:00',
            'clock_out' => '18:00',
            'break_start' => ['14:00'],
            'break_end' => ['15:00'],
            'note' => '追加申請テスト',
        ]);

        $response = $this->get(route('request.list'));
        $response->assertSee('追加申請テスト');
    }

    /**
     * ✅ 承認された申請がユーザーに表示されることを確認
     */
    public function test_approved_requests_are_visible_to_user()
    {
        // 1. ユーザーと管理者を作成
        $user = User::factory()->create(['is_admin' => false]); // 【修正】first()削除
        $admin = User::factory()->create(['is_admin' => true]); // 【修正】first()削除

        $this->actingAs($user);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $this->put(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '承認対象メモ',
        ]);

        // 2. 申請IDを取得し、管理者として承認
        $requestId = \App\Models\AttendanceRequest::where('user_id', $user->id)->first()->id;
        $this->actingAs($admin, 'admin'); // 【修正】adminガードを指定
        $this->post(route('admin.request.approve', ['id' => $requestId]));

        // 3. ユーザーとして再度ログインし、申請のステータスが承認済みであることを確認
        $this->actingAs($user);
        $response = $this->get(route('request.list'));
        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('承認対象メモ');
    }

    /**
     * ✅ 詳細ボタンをクリックして詳細画面に遷移できることを確認
     */
    public function test_clicking_detail_button_navigates_to_detail_screen()
    {
        // 1. ユーザーと管理者を作成
        $user = User::factory()->create(['is_admin' => false]); // 【修正】first()削除
        $admin = User::factory()->create(['is_admin' => true]); // 【修正】first()削除

        $this->actingAs($user);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $this->put(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '詳細表示用',
        ]);

        // 2. 管理者として詳細画面にアクセス
        $requestId = \App\Models\AttendanceRequest::where('user_id', $user->id)->first()->id;
        $this->actingAs($admin, 'admin'); // 【修正】adminガードを指定
        $response = $this->get(route('admin.request.show', ['id' => $requestId]));

        // 3. 詳細画面に正しい情報が表示されていることを確認
        $response->assertStatus(200);
        $response->assertSee('修正申請詳細');
        $response->assertSee('詳細表示用');
        $response->assertSee('09:00');
        $response->assertSee('17:00');
    }
}
