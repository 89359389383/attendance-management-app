<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceListAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 1. その日になされた全ユーザーの勤怠情報が正確に確認できる
     */
    public function test_admin_can_see_all_attendance_for_the_day()
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true]);

        // 2. 一般ユーザーを2名作成し、勤怠情報を登録
        $user1 = User::factory()->create(['name' => 'ユーザーA']);
        $user2 = User::factory()->create(['name' => 'ユーザーB']);

        Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => Carbon::today(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => '退勤済',
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => Carbon::today(),
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'status' => '退勤済',
        ]);

        // 3. 管理者としてログインし、勤怠一覧ページを開く
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list'));

        // 4. 画面上に2名のユーザーの名前が含まれていることを確認
        $response->assertStatus(200);
        $response->assertSee('ユーザーA');
        $response->assertSee('ユーザーB');
    }

    /**
     * ✅ 2. 遷移した際に現在の日付が表示される
     */
    public function test_current_date_is_displayed_on_attendance_list()
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true]);

        // 2. 今日の日付を取得（ビューでの表示形式に合わせる）
        $today = Carbon::today()->format('Y年m月d日');

        // 3. 管理者としてアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list'));

        // 4. 日付が画面上に正しく表示されていることを確認
        $response->assertStatus(200);
        $response->assertSee($today);
    }

    /**
     * ✅ 3. 「前日」ボタンで前日の勤怠情報が表示される
     */
    public function test_previous_day_button_displays_previous_day_data()
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true]);

        // 2. 前日の日付とユーザーを準備
        $yesterday = Carbon::yesterday()->toDateString();
        $user = User::factory()->create(['name' => '前日ユーザー']);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $yesterday,
            'clock_in' => '08:00',
            'clock_out' => '17:00',
            'status' => '退勤済',
        ]);

        // 3. 前日データ付きでアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list', ['date' => $yesterday]));

        // 4. 正しい勤怠情報が表示されているか確認
        $response->assertStatus(200);
        $response->assertSee('前日ユーザー');
        $response->assertSee(Carbon::parse($yesterday)->format('Y年m月d日'));
    }

    /**
     * ✅ 4. 「翌日」ボタンで翌日の勤怠情報が表示される
     */
    public function test_next_day_button_displays_next_day_data()
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true]);

        // 2. 翌日の日付とユーザーを準備
        $tomorrow = Carbon::tomorrow()->toDateString();
        $user = User::factory()->create(['name' => '翌日ユーザー']);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $tomorrow,
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'status' => '退勤済',
        ]);

        // 3. 翌日データ付きでアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list', ['date' => $tomorrow]));

        // 4. 翌日の日付とユーザー名が表示されていることを確認
        $response->assertStatus(200);
        $response->assertSee('翌日ユーザー');
        $response->assertSee(Carbon::parse($tomorrow)->format('Y年m月d日'));
    }
}
