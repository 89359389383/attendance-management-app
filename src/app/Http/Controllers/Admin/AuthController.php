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

        // emailに該当するユーザーが存在するか調査
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        if (!$user) {
            // ユーザーが存在しない場合の処理（ログ無し）
        }

        // 通常ログイン試行（adminガードでログイン）
        if (Auth::guard('admin')->attempt($credentials)) {
            // is_adminチェック
            if (Auth::guard('admin')->user()->is_admin) {
                return redirect()->intended('/admin/attendance/list');
            } else {
                // 管理者でないなら即ログアウト
                Auth::guard('admin')->logout();
            }
        } else {
            // パスワード不一致によるログイン失敗の処理（ログ無し）
        }

        // ★ここに来たらすべて失敗
        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }

    /**
     * 管理者ログアウト処理を行うメソッド
     * URL: /logout（ヘッダーなどに POST リンクとして配置）
     * メソッド: POST
     */
    public function logout()
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        return redirect('/admin/login')->with('success', 'ログアウトしました。');
    }
}
