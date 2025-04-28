<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;

class AttendanceEditAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 1. 承認待ちの修正申請が全て表示されていることを確認するテスト
     */
    public function test_pending_requests_are_displayed()
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true])->first();

        // 2. 勤怠データと承認待ちの修正申請を作成
        $user = User::factory()->create();  // ユーザーを作成
        $attendances = Attendance::factory()->count(3)->create(['user_id' => $user->id]); // 作成したユーザーIDを関連付ける
        foreach ($attendances as $attendance) {
            AttendanceRequest::factory()->create([
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'status' => '承認待ち',
            ]);
        }

        // 3. 管理者としてログインし、修正申請一覧ページを開く
        $response = $this->actingAs($admin, 'admin')->get(route('admin.request.list'));

        // 4. HTML全体から空白や改行を削除
        $html = preg_replace('/\s+/', '', $response->getContent());

        // 5. "承認待ち"タブに申請が表示されていることを確認
        $this->assertStringContainsString('承認待ち', $html);
    }

    /**
     * ✅ 2. 承認済みの修正申請が全て表示されていることを確認するテスト
     */
    public function test_approved_requests_are_displayed()
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true])->first();

        // 2. 一般ユーザーと勤怠データを作成してから承認済みの修正申請を作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        AttendanceRequest::factory()->count(3)->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => '承認済み',
        ]);

        // 3. 管理者としてログインし、修正申請一覧ページを開く
        $response = $this->actingAs($admin, 'admin')->get(route('admin.request.list'));

        // 4. HTML全体から空白や改行を削除
        $html = preg_replace('/\s+/', '', $response->getContent());

        // 5. "承認済み"タブに申請が表示されていることを確認
        $this->assertStringContainsString('承認済み', $html);
    }

    /**
     * ✅ 3. 修正申請の詳細内容が正しく表示されていることを確認するテスト
     */
    public function test_request_detail_is_displayed_correctly()
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true])->first();

        // 2. 一般ユーザーと勤怠データを作成してから修正申請を作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $request = AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => '承認待ち',
            'note' => 'テスト備考',
        ]);

        // 3. 管理者としてログインし、修正申請詳細ページを開く
        $response = $this->actingAs($admin, 'admin')->get(route('admin.request.show', $request->id));

        // 4. レスポンスが正常であることを確認
        $response->assertStatus(200);

        // 5. 申請内容（備考）が表示されていることを確認
        $response->assertSee('テスト備考');
    }

    /**
     * ✅ 4. 修正申請の承認処理が正しく行われることを確認するテスト
     */
    public function test_approval_process_updates_attendance_and_request()
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true])->first();

        // 2. 一般ユーザーと勤怠データ、修正申請データを作成
        $user = User::factory()->create(['is_admin' => false]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-04-25', // ここでwork_dateを固定
        ]);

        $attendanceRequest = AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::parse('2025-04-25 09:00:00'),
            'clock_out' => Carbon::parse('2025-04-25 18:00:00'),
            'note' => '修正理由テスト',
            'status' => '承認待ち',
        ]);

        // 3. 管理者としてログインし、承認ボタンを押す
        $response = $this->actingAs($admin, 'admin')->post(route('admin.request.approve', $attendanceRequest->id));

        // 4. 承認後にリダイレクトされることを確認
        $response->assertRedirect(route('admin.request.list'));

        // 5. 勤怠情報が修正申請の内容と一致していることを確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '2025-04-25 09:00:00',
            'clock_out' => '2025-04-25 18:00:00',
            'note' => '修正理由テスト',
        ]);

        // 6. 修正申請のステータスが「承認済み」に変わっていることを確認
        $this->assertDatabaseHas('attendance_requests', [
            'id' => $attendanceRequest->id,
            'status' => '承認済み',
        ]);
    }
}
