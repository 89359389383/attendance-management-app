<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 出勤・退勤の整合性
            'clock_in' => ['required', 'before:clock_out'],
            'clock_out' => ['required', 'after:clock_in'],

            // 休憩（複数の入力に対応するため .＊ を使用）
            'break_start.*' => ['nullable', 'after_or_equal:clock_in', 'before_or_equal:clock_out'],
            'break_end.*' => ['nullable', 'after_or_equal:clock_in', 'before_or_equal:clock_out'],

            // 備考
            'note' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            // 出退勤時刻の整合性
            'clock_in.before' => '出勤時間もしくは退勤時間が不適切な値です。',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です。',

            // 休憩時間の整合性（配列に対して適用）
            'break_start.*.after_or_equal' => '休憩時間が勤務時間外です。',
            'break_start.*.before_or_equal' => '休憩時間が勤務時間外です。',
            'break_end.*.after_or_equal' => '休憩時間が勤務時間外です。',
            'break_end.*.before_or_equal' => '休憩時間が勤務時間外です。',

            // 備考欄
            'note.required' => '備考を記入してください。',
        ];
    }
}
