<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceRequestController extends Controller
{
    /**
     * 修正申請一覧ページを表示するメソッド（承認待ち・承認済み）
     * URL: /stamp_correction_request/list
     * メソッド: GET
     * 認証: 管理者
     */
    public function index()
    {
        $pendingRequests = AttendanceRequest::with(['user', 'attendance'])
            ->where('status', '承認待ち')
            ->orderBy('request_date', 'desc')
            ->get();

        $approvedRequests = AttendanceRequest::with(['user', 'attendance'])
            ->where('status', '承認済み')
            ->orderBy('request_date', 'desc')
            ->get();

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
        Log::info("修正申請承認処理を開始：申請ID = $id");

        try {
            // 修正申請を取得
            $attendanceRequest = AttendanceRequest::findOrFail($id);
            Log::info('修正申請取得成功', ['request' => $attendanceRequest->toArray()]);

            // 勤怠情報を取得
            $attendance = Attendance::findOrFail($attendanceRequest->attendance_id);
            Log::info('勤怠情報取得成功', ['attendance' => $attendance->toArray()]);

            // 勤怠情報を修正申請の内容に更新
            $attendance->clock_in = $attendanceRequest->clock_in;
            $attendance->clock_out = $attendanceRequest->clock_out;
            $attendance->note = $attendanceRequest->note;
            $attendance->save();
            Log::info('勤怠情報を更新しました', ['updated_attendance' => $attendance->toArray()]);

            // 修正申請のステータスと承認者・承認日時を更新（※ 日本語に修正）
            $attendanceRequest->status = '承認済み';
            $attendanceRequest->approved_by = auth()->id();
            $attendanceRequest->approved_at = now();
            $attendanceRequest->save();
            Log::info('修正申請のステータスを承認済みに更新しました', ['updated_request' => $attendanceRequest->toArray()]);

            DB::commit();
            Log::info("修正申請承認処理が成功しました");

            return redirect()->route('admin.request.list')
                ->with('success', '修正申請を承認しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("修正申請承認処理に失敗", [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', '修正申請の承認に失敗しました。');
        }
    }

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
                Log::info('修正申請を取得', ['attendance_request_id' => $attendanceRequest->id]);

                // 対応する勤怠情報を取得（修正申請に紐づく勤怠IDで検索）
                $attendance = Attendance::findOrFail($attendanceRequest->attendance_id);
                Log::info('勤怠情報を取得', ['attendance_id' => $attendance->id]);

                // 勤怠情報を修正申請の内容に基づき更新
                $attendance->clock_in = $attendanceRequest->clock_in;
                $attendance->clock_out = $attendanceRequest->clock_out;
                $attendance->note = $attendanceRequest->note;
                // 更新を保存
                $attendance->save();
                Log::info('勤怠情報を更新しました', [
                    'attendance_id' => $attendance->id,
                    'clock_in' => $attendance->clock_in,
                    'clock_out' => $attendance->clock_out,
                    'note' => $attendance->note
                ]);

                // 修正申請のステータスを「承認済み」に変更
                $attendanceRequest->status = '承認済み';
                $attendanceRequest->approved_by = auth()->id(); // 承認者IDを設定
                $attendanceRequest->approved_at = now(); // 承認日時を設定
                // 更新を保存
                $attendanceRequest->save();
                Log::info('修正申請を承認済みに更新', [
                    'attendance_request_id' => $attendanceRequest->id,
                    'status' => $attendanceRequest->status,
                    'approved_by' => $attendanceRequest->approved_by,
                    'approved_at' => $attendanceRequest->approved_at
                ]);
            }

            // 全ての申請が正常に承認された場合、コミットして確定
            DB::commit();
            Log::info('一括承認処理が成功しました', ['approved_request_ids' => $ids]);

            // 承認が完了した旨のメッセージとともに一覧ページにリダイレクト
            return redirect()->route('admin.request.list')->with('success', '選択された修正申請をすべて承認しました。');
        } catch (\Exception $e) {
            // エラー発生時はロールバックして変更を元に戻す
            DB::rollBack();
            // エラー内容をログに記録
            Log::error('一括承認処理に失敗しました', [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_ids' => $ids
            ]);

            // エラーメッセージとともに元の画面に戻す
            return back()->with('error', '一括承認に失敗しました。');
        }
    }
}
