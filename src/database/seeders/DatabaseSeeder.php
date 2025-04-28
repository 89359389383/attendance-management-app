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
        // 【0】まず先にAdminUserSeederを実行（管理者・固定一般ユーザーだけ作る）
        $this->call(AdminUserSeeder::class);

        // 【1】固定一般ユーザーを取得（emailが 'test@example.com' のユーザー）
        $fixedUser = User::where('email', 'test@example.com')->first();

        if (!$fixedUser) {
            // 念のため：存在しない場合は処理を中断
            throw new \Exception('固定ユーザーが存在しません。AdminUserSeederが正しく動いているか確認してください。');
        }

        // 【2】一般ユーザーをさらに4人作成して、固定ユーザーを先頭に追加（合計5人）
        $users = User::factory(4)->create(['is_admin' => false])
            ->prepend($fixedUser);

        // 【3】それぞれのユーザーに、2025年2月1日〜4月19日の平日のみ勤怠データを作成
        foreach ($users as $user) {
            $period = CarbonPeriod::create('2025-02-01', '2025-04-19');

            foreach ($period as $date) {
                // 土日を除く
                if (in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                    continue;
                }

                // 勤怠レコード作成
                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'work_date' => $date->format('Y-m-d'),
                ]);

                // 2日ごとに休憩2件追加
                if ((int)$date->format('d') % 2 === 0) {
                    BreakTime::factory(2)->create([
                        'attendance_id' => $attendance->id,
                    ]);
                }

                // 3日ごとに修正申請作成
                if ((int)$date->format('d') % 3 === 0) {
                    $request = AttendanceRequest::factory()->create([
                        'user_id' => $user->id,
                        'attendance_id' => $attendance->id,
                    ]);

                    // 修正休憩時間も作成
                    RequestBreakTime::factory()->create([
                        'attendance_id' => $attendance->id,
                    ]);
                }
            }
        }
    }
}
