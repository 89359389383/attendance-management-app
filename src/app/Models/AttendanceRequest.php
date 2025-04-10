<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'request_date',
        'clock_in',
        'clock_out',
        'note',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'request_date' => 'date',         // 日付のみ（例: 2025-04-10）
        'clock_in'     => 'datetime',     // 日時（例: 2025-04-10 09:00:00）
        'clock_out'    => 'datetime',     // 日時（例: 2025-04-10 18:00:00）
        'approved_at'  => 'datetime',     // 日時（承認された時間）
    ];

    /**
     * 申請を出したユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 対象の勤怠情報
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * 承認した管理者（Userモデルを使う）
     */
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
