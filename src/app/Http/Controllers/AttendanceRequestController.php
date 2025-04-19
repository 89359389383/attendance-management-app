<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth; // 現在ログイン中のユーザー情報を取得するために使用
use App\Models\AttendanceRequest; // attendance_requestsテーブルと接続するモデル

class AttendanceRequestController extends Controller
{
    /**
     * 一般ユーザーが自分の修正申請を一覧表示するメソッド
     *
     * - 承認待ちと承認済みを分けて取得
     * - ビューでそれぞれタブやセクションに表示する前提
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // 修正①：「pending」→「承認待ち」、「approved」→「承認済み」
        $pendingRequests = AttendanceRequest::where('user_id', $user->id)
            ->where('status', '承認待ち') // ✅ 正しい値
            ->orderBy('request_date', 'desc')
            ->get();

        $approvedRequests = AttendanceRequest::where('user_id', $user->id)
            ->where('status', '承認済み') // ✅ 正しい値
            ->orderBy('request_date', 'desc')
            ->get();

        return view('attendance_request.index', [
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
        ]);
    }
}
