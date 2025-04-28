<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
use App\Models\RequestBreakTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 固定のパスワード文字列
        $adminPassword = 'p!N3x55$uM2y#Ft9';
        $userPassword = 'Kz8#rTq55@LmWv4z';

        // 【1】管理者ユーザーを作成（既に存在していれば更新）
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // 条件：このメールアドレスのユーザーがいるか
            [
                'name' => 'AdminUser', // 管理者の名前
                'password' => bcrypt($adminPassword),
                'is_admin' => true, // 管理者フラグをON
            ]
        );

        // 【2】固定の一般ユーザー（ログインテストやデータ確認用）を作成
        $fixedUser = User::updateOrCreate(
            ['email' => 'test@example.com'], // メールが同じユーザーがいれば更新
            [
                'name' => 'TestUser', // 表示名
                'password' => bcrypt($userPassword),
                'is_admin' => false, // 一般ユーザーとして設定
            ]
        );

        // 【3】一般ユーザーをさらに4人作成して、固定ユーザーを先頭に追加（合計5人）
        $users = User::factory(4)->create(['is_admin' => false])
            ->prepend($fixedUser);

        // 【4】それぞれのユーザーに、2025年2月1日〜4月19日の平日のみ勤怠データを作成
        foreach ($users as $user) {
            $period = CarbonPeriod::create('2025-02-01', '2025-04-19');

            foreach ($period as $date) {
                // 土日を除く（0 = 日曜, 6 = 土曜）
                if (in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                    continue;
                }

                // 勤怠レコード作成
                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'work_date' => $date->format('Y-m-d'),
                ]);

                // 【5】2日ごとに休憩を2件追加（偶数日）
                if ((int)$date->format('d') % 2 === 0) {
                    BreakTime::factory(2)->create([
                        'attendance_id' => $attendance->id,
                    ]);
                }

                // 【6】3日ごとに修正申請を作成（3で割り切れる日）
                if ((int)$date->format('d') % 3 === 0) {
                    $request = AttendanceRequest::factory()->create([
                        'user_id' => $user->id,
                        'attendance_id' => $attendance->id,
                    ]);

                    // 【7】対応する修正休憩時間の申請データも作成
                    RequestBreakTime::factory()->create([
                        'attendance_id' => $attendance->id,
                    ]);
                }
            }
        }
    }
}
