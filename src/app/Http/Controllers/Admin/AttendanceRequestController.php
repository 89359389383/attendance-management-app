<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceRequestController extends Controller
{
    /**
     * 修正申請一覧ページを表示するメソッド（承認待ち・承認済み）
     * URL: /admin/stamp_correction_request/list
     * メソッド: GET
     * 認証: 管理者
     */
    public function index(Request $request)
    {
        $name = $request->input('name');

        // ソート設定
        $sort = $request->input('sort', 'request_date'); // デフォルトソート項目
        $direction = $request->input('direction', 'desc'); // 昇順 or 降順

        // 並び替え可能なカラムのマッピング
        $sortable = [
            'name' => 'users.name',
            'work_date' => 'attendances.work_date',
            'request_date' => 'attendance_requests.request_date'
        ];
        $sortColumn = $sortable[$sort] ?? 'attendance_requests.request_date';

        // 承認待ち一覧の取得
        $pendingRequests = AttendanceRequest::query()
            ->join('users', 'attendance_requests.user_id', '=', 'users.id')
            ->join('attendances', 'attendance_requests.attendance_id', '=', 'attendances.id')
            ->where('attendance_requests.status', '承認待ち')
            ->when($name, function ($query) use ($name) {
                $query->where('users.name', 'like', "%{$name}%");
            })
            ->orderBy($sortColumn, $direction)
            ->select('attendance_requests.*')
            ->with(['user', 'attendance'])
            ->paginate(30);

        // 承認済み一覧の取得
        $approvedRequests = AttendanceRequest::query()
            ->join('users', 'attendance_requests.user_id', '=', 'users.id')
            ->join('attendances', 'attendance_requests.attendance_id', '=', 'attendances.id')
            ->where('attendance_requests.status', '承認済み')
            ->when($name, function ($query) use ($name) {
                $query->where('users.name', 'like', "%{$name}%");
            })
            ->orderBy($sortColumn, $direction)
            ->select('attendance_requests.*')
            ->with(['user', 'attendance'])
            ->paginate(30);

        // ビューに渡して表示
        return view('admin.attendance_request.index', compact('pendingRequests', 'approvedRequests'));
    }

    /**
     * 修正申請の詳細を表示するメソッド
     * URL: /stamp_correction_request/approve/{id}
     * メソッド: GET
     * 認証: 管理者
     */
    public function show($id)
    {
        // 該当する修正申請を取得（関連する勤怠情報と一緒に）
        $request = AttendanceRequest::with('attendance', 'user')->findOrFail($id);

        // 修正申請詳細ビューを返す（admin/attendance_request/approve.blade.php）
        return view('admin.attendance_request.approve', compact('request'));
    }

    /**
     * 修正申請の承認処理を行うメソッド
     * URL: /stamp_correction_request/approve/{id}
     * メソッド: POST
     * 認証: 管理者
     */
    public function approve(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // 修正申請を取得
            $attendanceRequest = AttendanceRequest::findOrFail($id);

            // 勤怠情報を取得
            $attendance = Attendance::findOrFail($attendanceRequest->attendance_id);

            // 勤怠情報を修正申請の内容に更新
            $attendance->clock_in = $attendanceRequest->clock_in;
            $attendance->clock_out = $attendanceRequest->clock_out;
            $attendance->note = $attendanceRequest->note;
            $attendance->save();

            // 修正申請のステータスと承認者・承認日時を更新
            $attendanceRequest->status = '承認済み';
            $attendanceRequest->approved_by = auth()->id();
            $attendanceRequest->approved_at = now();
            $attendanceRequest->save();

            DB::commit();

            return redirect()->route('admin.request.list')
                ->with('success', '修正申請を承認しました。');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', '修正申請の承認に失敗しました。');
        }
    }

    /**
     * 修正申請を一括承認するメソッド
     * URL: /stamp_correction_request/bulk_approve
     * メソッド: POST
     * 認証: 管理者
     */
    public function bulkApprove(Request $request)
    {
        // リクエストで選択された申請IDのリストを取得（デフォルトは空の配列）
        $ids = $request->input('request_ids', []);

        // 申請が1件も選択されていなかった場合
        if (empty($ids)) {
            // エラーメッセージを表示して元の画面に戻す
            return back()->with('error', '申請を1件以上選択してください。');
        }

        // トランザクション開始：複数のデータ更新をまとめて一括で行う
        DB::beginTransaction();

        try {
            // 各申請IDについて処理を行う
            foreach ($ids as $id) {
                // 修正申請を取得（IDで検索）
                $attendanceRequest = AttendanceRequest::findOrFail($id);

                // 対応する勤怠情報を取得（修正申請に紐づく勤怠IDで検索）
                $attendance = Attendance::findOrFail($attendanceRequest->attendance_id);

                // 勤怠情報を修正申請の内容に基づき更新
                $attendance->clock_in = $attendanceRequest->clock_in;
                $attendance->clock_out = $attendanceRequest->clock_out;
                $attendance->note = $attendanceRequest->note;
                // 更新を保存
                $attendance->save();

                // 修正申請のステータスを「承認済み」に変更
                $attendanceRequest->status = '承認済み';
                $attendanceRequest->approved_by = auth()->id(); // 承認者IDを設定
                $attendanceRequest->approved_at = now(); // 承認日時を設定
                // 更新を保存
                $attendanceRequest->save();
            }

            // 全ての申請が正常に承認された場合、コミットして確定
            DB::commit();

            // 承認が完了した旨のメッセージとともに一覧ページにリダイレクト
            return redirect()->route('admin.request.list')->with('success', '選択された修正申請をすべて承認しました。');
        } catch (\Exception $e) {
            // エラー発生時はロールバックして変更を元に戻す
            DB::rollBack();

            // エラーメッセージとともに元の画面に戻す
            return back()->with('error', '一括承認に失敗しました。');
        }
    }
}
