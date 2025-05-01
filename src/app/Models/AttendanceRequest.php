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

    /**
     * 勤怠修正申請に紐づく休憩時間情報（RequestBreakTime）を取得するリレーション
     *
     * ■ 何をしているか：
     * このメソッドは、「この修正申請に関連する休憩時間（request_break_timesテーブル）」を
     * まとめて取得するための設定です。
     *
     * ■ なぜ必要か：
     * 通常の休憩時間（break_times）は実際の打刻に基づいた情報ですが、
     * 修正申請では、申請時に入力された休憩時間（request_break_times）を別テーブルで管理しています。
     * そのため、修正申請に対して「どんな休憩時間の修正が出されているのか？」を確認したいときに使います。
     *
     * ■ 特殊な点：
     * 第2引数 'attendance_id'：RequestBreakTime テーブル内の外部キー
     * 第3引数 'attendance_id'：AttendanceRequest テーブル内の対応キー
     * → つまり、「勤怠情報ID（attendance_id）が一致するデータ同士を結びつける」設定です。
     * ※通常の主キー・外部キーではないので、手動でキーを指定しています。
     *
     * ■ 使用例：
     * $attendanceRequest->requestBreakTimes と記述することで、
     * その申請に対する全ての休憩時間修正情報を一覧で取得できます。
     */
    public function requestBreakTimes()
    {
        return $this->hasMany(RequestBreakTime::class, 'attendance_id', 'attendance_id');
    }
}
