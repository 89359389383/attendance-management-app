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
     * ✅ 1. 承認待ちの修正申請が表示されていることを確認
     */
    public function test_pending_requests_are_displayed()
    {
        // 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true])->first();

        // 一般ユーザーと修正申請を作成
        $user = User::factory()->create();
        $attendances = Attendance::factory()->count(2)->create(['user_id' => $user->id]);

        foreach ($attendances as $attendance) {
            AttendanceRequest::factory()->create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'status' => '承認待ち',
                'note' => '遅刻理由: 寝坊',
            ]);
        }

        // 管理者として修正申請一覧ページにアクセス（デフォルトは承認待ちタブ）
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.request.list'));

        $response->assertStatus(200);

        // 「承認待ち」申請が表示されていることだけを確認
        $response->assertSee('承認待ち');
        $response->assertSee('遅刻理由: 寝坊');
    }

    /**
     * ✅ 2. 承認済みの修正申請が表示されていることを確認
     */
    public function test_approved_requests_are_displayed()
    {
        // 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true])->first();

        // 一般ユーザーを2名作成
        $user1 = User::factory()->create(['name' => 'ユーザー太郎']);
        $user2 = User::factory()->create(['name' => 'ユーザー花子']);

        // 勤怠データを作成
        $attendance1 = Attendance::factory()->create(['user_id' => $user1->id]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user2->id]);

        // 承認済み修正申請を作成
        AttendanceRequest::factory()->create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'status' => '承認済み',
            'note' => '遅刻理由: 電車遅延',
        ]);

        AttendanceRequest::factory()->create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'status' => '承認済み',
            'note' => '早退理由: 通院',
        ]);

        // 承認済みタブを指定してアクセス
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.request.list', ['tab' => 'approved']));

        $response->assertStatus(200);

        // 承認済みの申請が表示されていることだけを確認
        $response->assertSee('承認済み');
        $response->assertSee('ユーザー太郎');
        $response->assertSee('遅刻理由: 電車遅延');
        $response->assertSee('ユーザー花子');
        $response->assertSee('早退理由: 通院');
    }

    /**
     * ✅ 3. 修正申請の詳細内容が表示されていることを確認
     */
    public function test_request_detail_is_displayed_correctly()
    {
        // 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true])->first();

        // 一般ユーザーと修正申請を作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $request = AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => '承認待ち',
            'note' => 'テスト備考',
        ]);

        // 詳細ページにアクセス
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.request.show', $request->id));

        $response->assertStatus(200);
        $response->assertSee('テスト備考');
    }

    /**
     * ✅ 4. 修正申請の承認処理が正しく行われることを確認
     */
    public function test_approval_process_updates_attendance_and_request()
    {
        // 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true])->first();

        // 一般ユーザーとデータ作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-04-25',
        ]);

        $request = AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => '承認待ち',
            'clock_in' => Carbon::parse('2025-04-25 09:00:00'),
            'clock_out' => Carbon::parse('2025-04-25 18:00:00'),
            'note' => '修正理由テスト',
        ]);

        // 承認処理を実行
        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.request.approve', $request->id));

        $response->assertRedirect(route('admin.request.list'));

        // 勤怠テーブルの更新を確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '2025-04-25 09:00:00',
            'clock_out' => '2025-04-25 18:00:00',
            'note' => '修正理由テスト',
        ]);

        // 修正申請テーブルの更新を確認
        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
            'status' => '承認済み',
        ]);
    }
}
