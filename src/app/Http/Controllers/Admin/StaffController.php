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
        $name = $request->input('name');
        $sort = $request->input('sort', 'name'); // デフォルトは名前順
        $direction = $request->input('direction', 'asc'); // デフォルトは昇順

        $query = User::where('is_admin', false);

        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        // 並び替え
        if (in_array($sort, ['name', 'email'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc'); // フォールバック
        }

        $staff = $query->get();

        return view('admin.staff.index', compact('staff'));
    }
}
