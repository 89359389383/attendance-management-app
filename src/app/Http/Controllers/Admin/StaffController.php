<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    /**
     * スタッフ一覧画面を表示する
     * URL: /admin/staff/list
     * メソッド: GET
     */
    public function index()
    {
        // 一般ユーザーのみ取得（is_admin = false）
        $staff = User::where('is_admin', false)->get();

        // スタッフ一覧ビューを返す
        return view('admin.staff.index', compact('staff'));
    }
}
