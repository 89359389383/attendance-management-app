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
        // 現在ログインしているユーザー情報を取得
        $user = Auth::user();

        // 承認待ち（statusが"pending"）の修正申請を取得し、申請日で降順に並べる
        $pendingRequests = AttendanceRequest::where('user_id', $user->id) // 自分の申請だけに限定
            ->where('status', 'pending') // 承認待ちのステータス
            ->orderBy('request_date', 'desc') // 日付が新しい順に並べる
            ->get(); // 実際にSQL実行して結果を取得

        // 承認済み（statusが"approved"）の修正申請を取得し、同様に並べる
        $approvedRequests = AttendanceRequest::where('user_id', $user->id) // 自分の申請のみ
            ->where('status', 'approved') // 承認済みのみを対象
            ->orderBy('request_date', 'desc') // 新しい順
            ->get(); // 結果を取得

        // attendance_request/index.blade.php ビューにデータを渡して表示
        // pendingRequests: 承認待ち一覧
        // approvedRequests: 承認済み一覧
        return view('attendance_request.index', [
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
        ]);
    }
}
