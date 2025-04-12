<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    /**
     * このリクエストが認証されているかどうかを確認します。
     * trueを返せばすべてのユーザーがこのリクエストを送信できます。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルールを定義します。
     * 入力データがこのルールに従っているかを確認します。
     */
    public function rules(): array
    {
        return [
            // 出勤時刻は必須、かつ退勤時刻より前である必要があります
            'clock_in' => ['required', 'before:clock_out'],

            // 退勤時刻は必須、かつ出勤時刻より後である必要があります
            'clock_out' => ['required', 'after:clock_in'],

            // 休憩開始時刻は任意ですが、出勤～退勤の間である必要があります
            'break_start' => ['nullable', 'after_or_equal:clock_in', 'before_or_equal:clock_out'],

            // 休憩終了時刻も同様に、出勤～退勤の間である必要があります
            'break_end' => ['nullable', 'after_or_equal:clock_in', 'before_or_equal:clock_out'],

            // 備考欄は入力必須
            'note' => ['required'],
        ];
    }

    /**
     * バリデーションエラーメッセージを定義します。
     * テストケースに合わせて、休憩時間エラーも共通メッセージで統一。
     */
    public function messages(): array
    {
        return [
            // 出勤・退勤の整合性エラー（共通）
            'clock_in.before' => '出勤時間もしくは退勤時間が不適切な値です。',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です。',

            // 休憩時間の整合性エラーも共通エラーメッセージで返す（テストケースに準拠）
            'break_start.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_start.before_or_equal' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_end.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_end.before_or_equal' => '出勤時間もしくは退勤時間が不適切な値です。',

            // 備考欄が未入力のエラー
            'note.required' => '備考を記入してください。',
        ];
    }
}
