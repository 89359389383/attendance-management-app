<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\PunchReminderMail;
use App\Models\User;
use Carbon\Carbon;

class SendPunchReminder extends Command
{
    protected $signature = 'reminder:send-punch';
    protected $description = '平日8:50に未打刻の固定ユーザーにリマインドメールを送る';

    public function handle()
    {
        // 固定ユーザー取得
        $user = User::where('email', 'test@example.com')->first();

        if (!$user) {
            $this->info('ユーザーが見つかりませんでした。');
            return;
        }

        // 今日の日付で出勤打刻がないか確認
        $today = Carbon::today();
        $hasClockIn = $user->attendances()
            ->whereDate('work_date', $today)
            ->whereNotNull('clock_in')
            ->exists();

        if (!$hasClockIn) {
            Mail::to($user->email)->send(new PunchReminderMail($user));
            $this->info('リマインドメールを送信しました。');
        } else {
            $this->info('すでに打刻済みです。');
        }
    }
}
