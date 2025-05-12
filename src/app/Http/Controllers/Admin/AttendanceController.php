<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\AdminAttendanceRequest;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 勤怠一覧画面を表示する（管理者）
     * URL: /admin/attendance/list
     * メソッド: GET
     */
    public function index(Request $request)
    {
        // リクエストパラメータの取得
        $date = $request->input('date', Carbon::today()->toDateString());
        $name = $request->input('name');
        $sort = $request->input('sort', 'users.name'); // 並び替え対象の初期値
        $direction = $request->input('direction', 'asc');

        // クエリビルダーの初期設定
        $query = Attendance::query()
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->where('work_date', $date)
            ->with(['user', 'breakTimes'])
            ->select('attendances.*'); // 必ず attendances.* を明示

        // 名前でのフィルタリング
        if ($name) {
            $query->where('users.name', 'like', "%{$name}%");
        }

        // 並び替え処理
        if (in_array($sort, ['users.name', 'attendances.clock_in', 'attendances.clock_out'])) {
            $query->orderBy($sort, $direction);
        }

        // データの取得
        $attendances = $query->get();

        // ビューにデータを渡す
        return view('admin.attendance.index', compact('attendances', 'date'));
    }

    /**
     * 勤怠詳細画面を表示する（管理者）
     * URL: /attendance/{id}
     * メソッド: GET
     */
    public function show($id)
    {
        // 指定されたIDの勤怠データを取得（関連するユーザーと休憩も）
        $attendance = Attendance::with(['user', 'breakTimes'])
            ->findOrFail($id);

        // 詳細画面を返す
        return view('admin.attendance.show', compact('attendance'));
    }

    /**
     * 勤怠データの更新（管理者による修正）
     * URL: /attendance/{id}
     * メソッド: PUT
     */
    public function update(AdminAttendanceRequest $request, $id)
    {
        // バリデーション処理は別ファイルに記載（ここでは省略）

        // 勤怠データを取得
        $attendance = Attendance::findOrFail($id);

        // 入力された情報で勤怠情報を更新
        $attendance->clock_in = $request->input('clock_in');
        $attendance->clock_out = $request->input('clock_out');
        $attendance->note = $request->input('note');
        $attendance->save();

        // 休憩時間の更新処理
        // 既存の休憩時間をすべて削除（再登録のため）
        $attendance->breakTimes()->delete();

        // 新しい休憩時間を再登録（複数入力を想定）
        $breakStarts = $request->input('break_start'); // 休憩開始時刻の配列を取得
        $breakEnds = $request->input('break_end');     // 休憩終了時刻の配列を取得

        // 両方の入力が配列として存在していることを確認
        if (is_array($breakStarts) && is_array($breakEnds)) {
            // 休憩開始時刻の配列をループで処理し、対応する終了時刻とセットで扱う
            foreach ($breakStarts as $index => $start) {
                // どちらも空でなければ（未入力でなければ）保存対象とする
                if (!empty($start) && !empty($breakEnds[$index])) {
                    // BreakTime モデルを使って新しい休憩時間レコードを作成
                    BreakTime::create([
                        'attendance_id' => $attendance->id, // この勤怠記録に紐づける外部キー
                        'break_start' => $start,             // フォームで入力された開始時刻
                        'break_end' => $breakEnds[$index],   // インデックスで対応する終了時刻を取得
                    ]);
                }
            }
        }

        // 一覧ページへリダイレクト
        return redirect()->route('admin.attendance.list', $attendance->id);
    }

    /**
     * スタッフ別の月次勤怠一覧を表示する
     * URL: /admin/attendance/staff/{id}
     * メソッド: GET
     */
    public function staffMonthlyList($id, Request $request)
    {
        // 指定されたユーザー情報を取得
        $user = User::findOrFail($id);

        // 表示する年月を取得（デフォルトは今月）
        $yearMonth = $request->input('month', Carbon::now()->format('Y-m'));

        // 指定月の勤怠情報を取得
        $attendances = Attendance::where('user_id', $id)
            ->where('work_date', 'like', "$yearMonth%")
            ->get();

        // ビューに渡す
        return view('admin.attendance.staff_monthly', compact('user', 'attendances', 'yearMonth'));
    }

    /**
     * ✅ 追加：月次勤怠CSV出力（admin.attendance.staff.export）
     * URL: /admin/attendance/staff/{id}/export
     * メソッド: GET
     */
    public function exportMonthlyCsv($id, Request $request)
    {
        $user = User::findOrFail($id);
        $yearMonth = $request->input('month', Carbon::now()->format('Y-m'));

        $attendances = Attendance::where('user_id', $id)
            ->where('work_date', 'like', "$yearMonth%")
            ->with('breakTimes')
            ->get();

        $csvHeader = ['日付', '出勤', '退勤', '休憩時間', '合計労働時間'];
        $csvData = [];

        $totalWorkMinutes = 0;
        $totalWorkDays = 0;

        foreach ($attendances as $attendance) {
            $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
            $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;

            $totalBreak = $attendance->breakTimes->sum(function ($break) {
                return $break->break_start && $break->break_end
                    ? Carbon::parse($break->break_end)->diffInMinutes(Carbon::parse($break->break_start))
                    : 0;
            });

            $breakFormatted = sprintf('%d:%02d', floor($totalBreak / 60), $totalBreak % 60);

            $workTotal = '';
            if ($clockIn && $clockOut) {
                $diff = $clockOut->diffInMinutes($clockIn) - $totalBreak;
                $totalWorkMinutes += max(0, $diff);
                $workTotal = sprintf('%d:%02d', floor($diff / 60), $diff % 60);
                $totalWorkDays++;
            }

            $csvData[] = [
                Carbon::parse($attendance->work_date)->format('Y/m/d(D)'),
                $clockIn ? $clockIn->format('H:i') : '',
                $clockOut ? $clockOut->format('H:i') : '',
                $breakFormatted,
                $workTotal,
            ];
        }

        $csvData[] = [];
        $csvData[] = ['勤務日数', $totalWorkDays . '日'];
        $csvData[] = ['合計勤務時間', sprintf('%d時間%02d分', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60)];

        $fileName = $user->name . '_月次勤怠_' . $yearMonth . '.csv';

        return response()->streamDownload(function () use ($csvHeader, $csvData) {
            $stream = fopen('php://output', 'w');
            fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($stream, $csvHeader);
            foreach ($csvData as $row) {
                fputcsv($stream, $row);
            }
            fclose($stream);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
