<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    /**
     * スタッフ一覧画面を表示する
     * URL: /admin/staff/list
     * メソッド: GET
     */
    public function index(Request $request)
    {
        $query = User::where('is_admin', false);

        // 検索キーワードがあれば部分一致で絞り込み
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $staff = $query->get();

        return view('admin.staff.index', compact('staff'));
    }
}
