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
     * URL: /stamp_correction_request/list
     * メソッド: GET
     * 認証: 管理者
     */
    public function index()
    {
        // 承認待ちの修正申請データを取得（status: 'pending'）
        $pendingRequests = AttendanceRequest::where('status', '承認待ち')->get();

        // 承認済みの修正申請データを取得（status: 'approved'）
        $approvedRequests = AttendanceRequest::where('status', '承認済み')->get();

        // 修正申請一覧ビューを返す（admin/attendance_request/index.blade.php）
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
        // トランザクションを開始（複数テーブルに関係する更新のため）
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
            $attendanceRequest->status = 'approved';
            $attendanceRequest->approved_by = auth()->id(); // 管理者のIDを保存
            $attendanceRequest->approved_at = now();
            $attendanceRequest->save();

            DB::commit(); // コミットして変更を確定

            // 承認完了メッセージ付きで一覧にリダイレクト
            return redirect()->route('admin.attendance_request.index')
                ->with('success', '修正申請を承認しました。');
        } catch (\Exception $e) {
            DB::rollBack(); // 失敗した場合はロールバック
            return back()->with('error', '修正申請の承認に失敗しました。');
        }
    }
}
