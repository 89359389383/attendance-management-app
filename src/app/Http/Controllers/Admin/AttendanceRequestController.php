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
}
