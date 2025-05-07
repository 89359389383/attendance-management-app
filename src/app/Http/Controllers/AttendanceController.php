<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest as AttendanceRequestModel;
use App\Http\Requests\AttendanceRequest;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Log;
use App\Models\RequestBreakTime; // ★【追加】修正申請用の休憩テーブル用モデルを読み込む
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 勤怠登録画面を表示するメソッド
     * URL: /attendance
     * メソッド: GET
     */
    public function show()
    {
        // 現在ログインしているユーザーの情報を取得する
        $user = Auth::user();

        // 今日の日付を「YYYY-MM-DD」形式の文字列で取得する（例：2025-04-18）
        $today = now()->toDateString();

        // ログイン中のユーザーが今日すでに打刻している勤怠データを取得する
        // ・user_id がログインユーザーのIDと一致
        // ・work_date が今日の日付と一致
        // 条件に一致する最初の1件を取得（存在しない場合は null が返る）
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();
        return view('attendance.create', compact('attendance'));
    }

    /**
     * 勤怠打刻の処理を実行するメソッド
     * URL: /attendance
     * メソッド: POST
     */
    public function store(Request $request)
    {
        // ログイン中のユーザー情報を取得
        $user = Auth::user();

        // 今日の日付を取得
        $today = now()->toDateString();

        // 今日の勤怠情報を取得 or 作成（初回打刻のみ作成）
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['status' => '勤務外']
        );

        // 打刻の種類（出勤・休憩・休憩戻・退勤）を取得
        $action = $request->input('action');

        // 出勤処理（勤務外 → 出勤中）
        if ($action === 'clock_in' && $attendance->status === '勤務外') {
            $attendance->clock_in = now(); // 出勤時刻を記録
            $attendance->status = '出勤中'; // ステータス更新
            $attendance->save(); // 保存
            return redirect()->back()->with('success', '出勤しました。');
        }

        // 休憩開始処理（出勤中 → 休憩中）
        if ($action === 'break_start' && $attendance->status === '出勤中') {
            BreakTime::create([
                'attendance_id' => $attendance->id, // 勤怠レコードと紐づけ
                'break_start' => now(), // 休憩開始時刻
            ]);
            $attendance->status = '休憩中'; // ステータス更新
            $attendance->save();
            return redirect()->back()->with('success', '休憩に入りました。');
        }

        // 休憩終了処理（休憩中 → 出勤中）
        if ($action === 'break_end' && $attendance->status === '休憩中') {
            $break = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->latest()
                ->first();

            if ($break) {
                $break->break_end = now(); // 休憩終了時刻
                $break->save();
            }
            $attendance->status = '出勤中'; // ステータス更新
            $attendance->save();
            return redirect()->back()->with('success', '休憩を終了しました。');
        }

        // 退勤処理（出勤中 → 退勤済）
        if ($action === 'clock_out' && $attendance->status === '出勤中') {
            $attendance->clock_out = now(); // 退勤時刻を記録
            $attendance->status = '退勤済'; // ステータス更新
            $attendance->save();
            return redirect()->back();
        }

        // 条件に合致しない場合の処理
        return redirect()->back()->with('error', '不正な打刻処理です。');
    }

    /**
     * 勤怠一覧を表示するメソッド
     * URL: /attendance/list
     * メソッド: GET
     */
    public function index(Request $request)
    {
        // 現在ログインしているユーザーのIDを取得（自分自身の勤怠データだけ表示するため）
        $userId = Auth::id();

        // URLクエリに「month=YYYY-MM」形式の指定がある場合はその月を、なければ現在の月（今月）を基準にする
        $currentMonth = $request->input('month')
            ? \Carbon\Carbon::createFromFormat('Y-m', $request->input('month'))->startOfMonth() // 指定された月の初日をCarbonで作成
            : now()->startOfMonth(); // 月指定がない場合は今月の初日を取得

        // 当月の開始日を文字列（例："2024-09-01"）で取得（勤怠データ抽出の開始日）
        $startDate = $currentMonth->copy()->startOfMonth()->toDateString();

        // 当月の終了日を文字列（例："2024-09-30"）で取得（勤怠データ抽出の終了日）
        $endDate = $currentMonth->copy()->endOfMonth()->toDateString();

        // 勤怠テーブル（attendances）から、ログイン中のユーザーの指定月の勤怠データを取得
        // 「出勤」「退勤」「休憩」などの情報がある `breakTimes` リレーションも一緒に取得する
        // さらに、日付順（昇順）に並び替えることで、一覧がカレンダーのように表示されるようにする
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $userId) // 自分の勤怠情報だけに絞る
            ->whereBetween('work_date', [$startDate, $endDate]) // 指定月の日付範囲に絞る
            ->orderBy('work_date', 'asc') // 日付の昇順で並べる（01日→31日）
            ->get(); // 実際にデータベースから取得

        // ログ出力（休憩情報含む） ← この部分を追加
        foreach ($attendances as $attendance) {
            Log::info("【勤怠記録】", [
                '勤務日' => $attendance->work_date,
                '出勤時刻' => $attendance->clock_in,
                '退勤時刻' => $attendance->clock_out,
                'ステータス' => $attendance->status,
            ]);

            if ($attendance->breakTimes->isEmpty()) {
                Log::info("　→ 休憩記録なし");
            } else {
                foreach ($attendance->breakTimes as $index => $break) {
                    Log::info("　→ 休憩{$index}：", [
                        '開始' => $break->break_start,
                        '終了' => $break->break_end,
                        '休憩時間（分）' => $break->break_start && $break->break_end
                            ? \Carbon\Carbon::parse($break->break_end)->diffInMinutes(\Carbon\Carbon::parse($break->break_start))
                            : '未完了 or 不明',
                    ]);
                }
            }
        }

        // ビュー（resources/views/attendance/index.blade.php）に、取得した勤怠一覧と現在表示中の月を渡す
        return view('attendance.index', compact('attendances', 'currentMonth'));
    }

    /**
     * 勤怠詳細画面を表示するメソッド
     * URL: /attendance/{id}
     * メソッド: GET
     */
    public function showDetail($id)
    {
        $attendance = Attendance::with(['breakTimes', 'user'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $attendanceRequest = AttendanceRequestModel::where('attendance_id', $attendance->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->first();

        return view('attendance.show', compact('attendance', 'attendanceRequest'));
    }

    /**
     * 勤怠修正申請の更新処理を行うメソッド
     * URL: /attendance/{id}
     * メソッド: PUT
     */
    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // ステータスを「修正申請中」に更新
        $attendance->status = '修正申請中';
        $attendance->save();

        // ログ出力：修正申請の開始
        Log::info('修正申請が開始されました', [
            'ユーザーID' => Auth::id(),
            '勤怠ID' => $attendance->id,
            'リクエストデータ' => $request->all(),
        ]);

        // ★変更箇所：既に承認待ちの修正申請があるかを確認
        $existingRequest = \App\Models\AttendanceRequest::where('attendance_id', $attendance->id)
            ->where('user_id', Auth::id())
            ->where('status', '承認待ち')
            ->latest()
            ->first();

        if ($existingRequest) {
            // ★変更箇所：既存の申請を上書き更新
            $existingRequest->update([
                'clock_in' => $request->clock_in,
                'clock_out' => $request->clock_out,
                'note' => $request->note,
                'request_date' => now()->toDateString(),
            ]);

            // ★変更箇所：既存の休憩申請を削除
            RequestBreakTime::where('attendance_id', $attendance->id)->delete();
        } else {
            // 初回申請：新規作成
            \App\Models\AttendanceRequest::create([
                'user_id' => Auth::id(),
                'attendance_id' => $attendance->id,
                'request_date' => now()->toDateString(),
                'clock_in' => $request->clock_in,
                'clock_out' => $request->clock_out,
                'note' => $request->note,
                'status' => '承認待ち',
            ]);
        }

        // 共通処理：休憩申請の再登録
        $breakStarts = $request->input('break_start', []);
        $breakEnds = $request->input('break_end', []);

        foreach ($breakStarts as $index => $startTime) {
            $endTime = $breakEnds[$index] ?? null;
            if ($startTime || $endTime) {
                RequestBreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $startTime,
                    'break_end' => $endTime,
                ]);

                // ログ出力：休憩申請の追加
                Log::info('休憩申請が追加されました', [
                    '勤怠ID' => $attendance->id,
                    '休憩開始' => $startTime,
                    '休憩終了' => $endTime,
                ]);
            }
        }

        // ログ出力：修正申請の完了
        Log::info('修正申請が完了しました', [
            '勤怠ID' => $attendance->id,
        ]);

        return redirect()->route('request.list')->with('success', '修正申請が完了しました。');
    }
}
