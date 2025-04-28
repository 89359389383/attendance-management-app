<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class UserInfoAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 1. 管理者が全一般ユーザーの氏名とメールアドレスを確認できる
     */
    public function test_admin_can_view_all_staff_names_and_emails()
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true]);

        // 2. 一般ユーザーを複数作成
        $user1 = User::factory()->create(['name' => '一般ユーザー1', 'email' => 'user1@example.com']);
        $user2 = User::factory()->create(['name' => '一般ユーザー2', 'email' => 'user2@example.com']);

        // 3. 管理者としてログインしスタッフ一覧ページにアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.staff.list'));

        // 4. 各ユーザーの氏名とメールアドレスが表示されていることを確認
        $response->assertStatus(200);
        $response->assertSee('一般ユーザー1');
        $response->assertSee('user1@example.com');
        $response->assertSee('一般ユーザー2');
        $response->assertSee('user2@example.com');
    }

    /**
     * ✅ 2. 選択したユーザーの月次勤怠情報が正しく表示される
     */
    public function test_admin_can_view_monthly_attendance_for_selected_user()
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true]);

        // 2. 一般ユーザーと勤怠データを作成
        $user = User::factory()->create(['name' => '勤怠ユーザー']);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->startOfMonth(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => '退勤済',
        ]);

        // 3. 管理者として対象ユーザーの月次勤怠一覧ページにアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.staff', ['id' => $user->id]));

        // 4. 勤怠ユーザーの名前と出勤・退勤時間が含まれていることを確認
        $response->assertStatus(200);
        $response->assertSee('勤怠ユーザー');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * ✅ 3. 「前月」ボタンで前月の情報が表示される
     */
    public function test_previous_month_button_displays_previous_month_data()
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true]);

        // 2. 前月の日付と勤怠情報を持つユーザーを作成
        $user = User::factory()->create(['name' => '前月ユーザー']);
        $lastMonth = Carbon::now()->subMonth()->format('Y-m');
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $lastMonth . '-10',
            'clock_in' => '08:30',
            'clock_out' => '17:30',
            'status' => '退勤済',
        ]);

        // 3. 管理者が前月の勤怠データを閲覧
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => $lastMonth,
        ]));

        // 4. 勤怠内容とユーザー名が表示されているか確認
        $response->assertStatus(200);
        $response->assertSee('前月ユーザー');
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }

    /**
     * ✅ 4. 「翌月」ボタンで翌月の情報が表示される
     */
    public function test_next_month_button_displays_next_month_data()
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true]);

        // 2. 翌月の勤怠情報を持つユーザーを作成
        $user = User::factory()->create(['name' => '翌月ユーザー']);
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $nextMonth . '-05',
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'status' => '退勤済',
        ]);

        // 3. 管理者が翌月の勤怠データを閲覧
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => $nextMonth,
        ]));

        // 4. 勤怠内容とユーザー名が表示されているか確認
        $response->assertStatus(200);
        $response->assertSee('翌月ユーザー');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /**
     * ✅ 5. 「詳細」ボタンから勤怠詳細画面に遷移できる
     */
    public function test_clicking_detail_link_redirects_to_attendance_detail()
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true]);

        // 2. 勤怠データを持つユーザーを作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => '退勤済',
        ]);

        // 3. 詳細リンクにアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.detail', ['id' => $attendance->id]));

        // 4. 勤怠詳細画面が正常に表示されていることを確認
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
