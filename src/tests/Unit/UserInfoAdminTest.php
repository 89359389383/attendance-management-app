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
        // 手順1: 管理者ユーザーを作成
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['is_admin' => true]);

        // 手順2: 一般ユーザーを複数作成
        $user1 = User::factory()->create(['name' => '一般ユーザー1', 'email' => 'user1@example.com']);
        $user2 = User::factory()->create(['name' => '一般ユーザー2', 'email' => 'user2@example.com']);

        // 手順3: 管理者としてログインし、スタッフ一覧ページにアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.staff.list'));

        // 手順4: ユーザーの氏名とメールアドレスが表示されていることを確認
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
        // 手順1: 管理者ユーザーを作成
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['is_admin' => true]);

        // 手順2: 勤怠データを持つ一般ユーザーを作成
        $user = User::factory()->create(['name' => '勤怠ユーザー']);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->startOfMonth(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => '退勤済',
        ]);

        // 手順3: 管理者としてログインする
        $this->actingAs($admin, 'admin');

        // 手順4: 対象ユーザーの月次勤怠一覧ページにアクセス
        $response = $this->get(route('admin.attendance.staff', ['id' => $user->id]));

        // 手順5: 勤怠ユーザーの名前と出退勤時刻が表示されていることを確認
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
        // 手順1: 管理者ユーザーを作成
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['is_admin' => true]);

        // 手順2: 前月の勤怠データを持つユーザーを作成
        $user = User::factory()->create(['name' => '前月ユーザー']);
        $lastMonth = Carbon::now()->subMonth()->format('Y-m');
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $lastMonth . '-10',
            'clock_in' => '08:30',
            'clock_out' => '17:30',
            'status' => '退勤済',
        ]);

        // 手順3: 管理者としてログインする
        $this->actingAs($admin, 'admin');

        // 手順4: 「前月」パラメータ付きで勤怠一覧ページにアクセスする
        $response = $this->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => $lastMonth,
        ]));

        // 手順5: 勤怠内容とユーザー名が表示されていることを確認
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
        // 手順1: 管理者ユーザーを作成
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['is_admin' => true]);

        // 手順2: 翌月の勤怠データを持つユーザーを作成
        $user = User::factory()->create(['name' => '翌月ユーザー']);
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $nextMonth . '-05',
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'status' => '退勤済',
        ]);

        // 手順3: 管理者としてログインする
        $this->actingAs($admin, 'admin');

        // 手順4: 「翌月」パラメータ付きで勤怠一覧ページにアクセスする
        $response = $this->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => $nextMonth,
        ]));

        // 手順5: 勤怠内容とユーザー名が表示されていることを確認
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
        // 手順1: 管理者ユーザーを作成
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['is_admin' => true]);

        // 手順2: 勤怠データを持つユーザーを作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => '退勤済',
        ]);

        // 手順3: 管理者としてログインする
        $this->actingAs($admin, 'admin');

        // 手順4: 「詳細」リンクにアクセスする
        $response = $this->get(route('admin.attendance.detail', ['id' => $attendance->id]));

        // 手順5: 勤怠詳細画面が正常に表示されていることを確認
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
