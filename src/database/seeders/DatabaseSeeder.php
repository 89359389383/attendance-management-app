<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
use App\Models\RequestBreakTime;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 【1】管理者ユーザーを作成（既に存在していれば更新）
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // 条件：このメールアドレスのユーザーがいるか
            [
                'name' => 'AdminUser', // 管理者の名前
                'password' => bcrypt('adminpassword'), // パスワードをハッシュ化して保存
                'is_admin' => true, // 管理者フラグをON
            ]
        );

        // 【2】固定の一般ユーザー（ログインテストやデータ確認用）を作成
        $fixedUser = User::updateOrCreate(
            ['email' => 'test@example.com'], // メールが同じユーザーがいれば更新
            [
                'name' => 'TestUser', // 表示名
                'password' => bcrypt('password19980614'), // 固定パスワード
                'is_admin' => false, // 一般ユーザーとして設定
            ]
        );

        // 【3】一般ユーザーをさらに4人作成して、固定ユーザーを先頭に追加（合計5人）
        $users = User::factory(4)->create(['is_admin' => false]) // ランダムユーザー4名
            ->prepend($fixedUser); // 先頭にテストユーザーを追加（結果：5名）

        // 【4】それぞれのユーザーに勤怠データを付与
        foreach ($users as $user) {
            // 3ヶ月分（直近3ヶ月）の勤怠データを生成
            foreach (range(1, 3) as $monthOffset) {
                // 各月に対して5日分の勤怠データを作成
                foreach (range(1, 5) as $i) {
                    // 勤怠日を、各月の1日目から数えて i 日目に設定
                    $date = now()->subMonths($monthOffset)->startOfMonth()->addDays($i);

                    // 勤怠レコードを作成（ユーザーごと・日付ごと）
                    $attendance = Attendance::factory()->create([
                        'user_id' => $user->id, // このユーザーに紐付け
                        'work_date' => $date->format('Y-m-d'), // 勤務日を明示的に指定
                    ]);

                    // 【5】勤怠の中で、2日ごとに休憩を2件追加（複数回休憩のテスト用）
                    if ($i % 2 === 0) {
                        BreakTime::factory(2)->create([
                            'attendance_id' => $attendance->id, // 対象の勤怠IDに紐付け
                        ]);
                    }

                    // 【6】3日ごとに修正申請を作成（承認待ちと承認済みを混在）
                    if ($i % 3 === 0) {
                        $request = AttendanceRequest::factory()->create([
                            'user_id' => $user->id, // 修正申請者
                            'attendance_id' => $attendance->id, // 対象の勤怠
                        ]);

                        // 【7】対応する修正休憩時間の申請データも作成
                        RequestBreakTime::factory()->create([
                            'attendance_id' => $attendance->id, // 同じ勤怠に紐付ける
                        ]);
                    }
                }
            }
        }
    }
}
