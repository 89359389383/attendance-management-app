<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin', // 管理者フラグ
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    /**
     * 勤怠情報（1対多）
     * 一般ユーザーが登録した勤怠データとのリレーション
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * 勤怠修正申請（1対多）
     * 一般ユーザーが提出した修正申請データとのリレーション
     */
    public function attendanceRequests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }

    /**
     * 管理者が承認した修正申請（1対多）
     *
     * ■ 機能：
     * このメソッドは、"自分が承認した勤怠修正申請" を取得するためのリレーションです。
     *
     * ■ メリット：
     * ・承認済み申請の取得を簡潔に記述できる
     * ・クエリの再利用性・可読性が高まる
     * ・管理者ログや承認履歴の表示で非常に役立つ
     *
     * ■ 使用が想定される場面：
     * ・管理者ユーザーが承認したすべての申請を一覧で確認する画面
     * ・ダッシュボードで「今日承認した申請は○件」のような集計表示
     * ・管理者の行動ログや統計分析（承認数ランキングなど）
     *
     * ■ リレーション定義のポイント：
     * ・外部キー `approved_by` は User モデルのIDを参照している
     * ・そのため第2引数に `'approved_by'` を指定する必要がある
     */
    public function approvedRequests()
    {
        return $this->hasMany(AttendanceRequest::class, 'approved_by');
    }
}
