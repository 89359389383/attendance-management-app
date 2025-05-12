<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * 会員登録フォームを表示するメソッド
     * URL: /register
     * メソッド: GET
     */
    public function showRegisterForm()
    {
        // 会員登録用のビューを返す
        return view('auth.register');
    }

    /**
     * 新しいユーザーを登録するメソッド
     * URL: /register
     * メソッド: POST
     */
    public function store(RegisterRequest $request)
    {
        try {
            // ユーザーを作成
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'is_admin' => false,
            ]);

            // ユーザーを自動ログイン
            Auth::login($user);

            // 認証メールを送信
            $user->sendEmailVerificationNotification();

            // メール認証ページにリダイレクト
            return redirect()->route('verification.notice');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['register' => '登録処理中にエラーが発生しました']);
        }
    }

    /**
     * ログインフォームを表示するメソッド
     * URL: /login
     * メソッド: GET
     */
    public function showLoginForm()
    {
        // ログイン用のビューを返す
        return view('auth.login');
    }

    /**
     * ユーザーをログインさせるメソッド
     * URL: /login
     * メソッド: POST
     */
    public function login(LoginRequest $request)
    {
        // フォームから送信されたメールアドレスとパスワードを取得
        $credentials = $request->only('email', 'password');

        // 入力された資格情報でログイン処理を実行
        if (Auth::attempt($credentials)) {
            // ログイン成功時、/attendance にリダイレクト
            return redirect()->intended('/attendance');
        }

        // ログイン失敗時、ログイン画面に戻りエラーメッセージを表示
        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }

    /**
     * ユーザーをログアウトさせるメソッド
     * URL: /logout
     * メソッド: POST
     */
    public function logout()
    {
        $redirectUrl = '/login'; // デフォルトは一般ユーザー用

        if (auth()->check() && auth()->user()->is_admin) {
            $redirectUrl = '/admin/login'; // 管理者なら管理者ログインへ
        }

        Auth::logout();

        return redirect($redirectUrl)->with('success', 'ログアウトしました。');
    }
}
