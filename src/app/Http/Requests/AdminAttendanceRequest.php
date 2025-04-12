<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceRequest extends FormRequest
{
    /**
     * このリクエストが認証されているかどうかを定義します。
     * 管理者ログイン済みであることを前提として、true を返します。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルールを定義します。
     * 管理者側から修正する場合も、一般ユーザーと同じ整合性が必要です。
     */
    public function rules(): array
    {
        return [
            // 出勤時刻は必須、退勤時刻より前であること
            'clock_in' => ['required', 'before:clock_out'],

            // 退勤時刻は必須、出勤時刻より後であること
            'clock_out' => ['required', 'after:clock_in'],

            // 休憩開始時刻は任意だが、出勤〜退勤の範囲であること
            'break_start' => ['nullable', 'after_or_equal:clock_in', 'before_or_equal:clock_out'],

            // 休憩終了時刻も同様に範囲内であること
            'break_end' => ['nullable', 'after_or_equal:clock_in', 'before_or_equal:clock_out'],

            // 備考欄は必須
            'note' => ['required'],
        ];
    }

    /**
     * バリデーションエラーメッセージを定義します。
     * 一般ユーザーと同様のメッセージ仕様に従って統一します。
     */
    public function messages(): array
    {
        return [
            // 出退勤の整合性エラー（共通）
            'clock_in.before' => '出勤時間もしくは退勤時間が不適切な値です。',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です。',

            // 休憩時刻が出勤〜退勤の範囲外である場合も共通メッセージを使用
            'break_start.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_start.before_or_equal' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_end.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_end.before_or_equal' => '出勤時間もしくは退勤時間が不適切な値です。',

            // 備考が未入力のとき
            'note.required' => '備考を記入してください。',
        ];
    }
}
