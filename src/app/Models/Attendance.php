<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'note',
        'status',
    ];

    /**
     * ユーザー（多対1）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 休憩時間（1対多）
     */
    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    /**
     * 修正申請（1対多）
     */
    public function attendanceRequests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }
}
