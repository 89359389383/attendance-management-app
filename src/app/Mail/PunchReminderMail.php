<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class PunchReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->from('no-reply@example.com', '勤怠管理アプリ')
            ->subject('【勤怠打刻のご案内】')
            ->view('emails.punch_reminder')
            ->with(['user' => $this->user]);
    }
}
