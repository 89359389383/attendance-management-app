<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequest;

class AttendanceController extends Controller
{
    /**
     * 勤怠登録画面を表示するメソッド
     * URL: /attendance
     * メソッド: GET
     */
    public function show()
    {
        // 勤怠打刻用のフォームビューを表示
        return view('attendance.create');
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
            return redirect()->back()->with('success', 'お疲れ様でした。');
        }

        // 条件に合致しない場合の処理
        return redirect()->back()->with('error', '不正な打刻処理です。');
    }

    /**
     * 勤怠一覧を表示するメソッド
     * URL: /attendance/list
     * メソッド: GET
     */
    public function index()
    {
        // ログイン中のユーザーの勤怠データを取得し、日付の降順で並び替え
        $attendances = Attendance::where('user_id', Auth::id())->orderBy('work_date', 'asc')->get();

        // 勤怠一覧ビューを表示（データを渡す）
        return view('attendance.index', compact('attendances'));
    }

    /**
     * 勤怠詳細画面を表示するメソッド
     * URL: /attendance/{id}
     * メソッド: GET
     */
    public function showDetail($id)
    {
        // ログイン中ユーザーの指定勤怠データを取得（存在しない場合は404）
        $attendance = Attendance::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // 勤怠詳細ビューにデータを渡して表示
        return view('attendance.show', compact('attendance'));
    }

    /**
     * 勤怠修正申請の更新処理を行うメソッド
     * URL: /attendance/{id}
     * メソッド: PUT
     */
    public function update(AttendanceRequest $request, $id)
    {
        // ログイン中ユーザーが対象の勤怠データを取得
        $attendance = Attendance::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // 出勤時刻を更新
        $attendance->clock_in = $request->clock_in;
        // 退勤時刻を更新
        $attendance->clock_out = $request->clock_out;
        // 備考を更新
        $attendance->note = $request->note;
        // 修正申請中のステータスに変更
        $attendance->status = '修正申請中';
        // レコードを保存
        $attendance->save();

        // 勤怠一覧ページへリダイレクト
        return redirect()->route('attendance.list')->with('success', '修正申請が完了しました。');
    }
}
