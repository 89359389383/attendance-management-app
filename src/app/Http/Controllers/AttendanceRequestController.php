<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth; // 現在ログイン中のユーザー情報を取得するために使用
use App\Models\AttendanceRequest; // attendance_requestsテーブルと接続するモデル
use Illuminate\Http\Request;

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
    public function index(Request $request)
    {
        $sort = $request->input('sort', 'request_date');
        $direction = $request->input('direction', 'desc');
        $name = $request->input('name');

        $sortable = [
            'name' => 'users.name',
            'work_date' => 'attendances.work_date',
            'request_date' => 'attendance_requests.request_date'
        ];
        $sortColumn = $sortable[$sort] ?? 'attendance_requests.request_date';

        // 承認待ち
        $pendingRequests = AttendanceRequest::query()
            ->join('users', 'attendance_requests.user_id', '=', 'users.id')
            ->join('attendances', 'attendance_requests.attendance_id', '=', 'attendances.id')
            ->when($name, fn($query) => $query->where('users.name', 'like', "%{$name}%"))
            ->where('attendance_requests.status', '承認待ち')
            ->orderBy($sortColumn, $direction)
            ->select('attendance_requests.*')
            ->with(['user', 'attendance'])
            ->get();

        // 承認済み
        $approvedRequests = AttendanceRequest::query()
            ->join('users', 'attendance_requests.user_id', '=', 'users.id')
            ->join('attendances', 'attendance_requests.attendance_id', '=', 'attendances.id')
            ->when($name, fn($query) => $query->where('users.name', 'like', "%{$name}%"))
            ->where('attendance_requests.status', '承認済み')
            ->orderBy($sortColumn, $direction)
            ->select('attendance_requests.*')
            ->with(['user', 'attendance'])
            ->get();

        return view('admin.attendance_request.index', compact('pendingRequests', 'approvedRequests'));
    }
}
