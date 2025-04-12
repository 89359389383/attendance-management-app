<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    /**
     * 管理者ログインフォームを表示するメソッド
     * URL: /admin/login
     * メソッド: GET
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * 管理者ログイン処理を行うメソッド
     * URL: /admin/login
     * メソッド: POST
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // 「is_admin」が true（管理者）であることも確認
        $credentials['is_admin'] = true;

        if (Auth::attempt($credentials)) {
            return redirect()->intended('/admin/attendance/list');
        }

        // ログイン失敗時のエラーメッセージを返す
        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }

    /**
     * 管理者ログアウト処理を行うメソッド
     * URL: /logout（ヘッダーなどに POST リンクとして配置）
     * メソッド: POST
     */
    public function logout()
    {
        Auth::logout();

        return redirect('/admin/login')->with('success', 'ログアウトしました。');
    }
}
