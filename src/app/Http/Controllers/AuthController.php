<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            // 入力されたデータを元にユーザーを作成
            Log::info('ユーザー登録処理開始', [
                'name' => $request->name,
                'email' => $request->email,
            ]);

            $user = User::create([
                'name' => $request->name, // ユーザー名を保存
                'email' => $request->email, // メールアドレスを保存
                'password' => bcrypt($request->password), // パスワードをハッシュ化して保存
            ]);

            // ユーザー作成後のログ
            Log::info('ユーザー登録完了', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);

            // 作成したユーザーをログイン状態にする
            Auth::login($user);

            // ログイン成功後のログ
            Log::info('ユーザーログイン成功', [
                'user_id' => $user->id,
                'name' => $user->name,
            ]);

            // 勤怠登録ページにリダイレクト
            Log::info('ユーザー登録後、勤怠登録ページにリダイレクト', [
                'route' => route('attendance.show')
            ]);

            return redirect()->route('attendance.show');
        } catch (\Exception $e) {
            // エラーログを記録
            Log::error('ユーザー登録中にエラーが発生', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            // エラーページにリダイレクトまたは適切な処理
            return back()->withErrors(['error' => 'ユーザー登録に失敗しました。']);
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
        // 現在ログイン中のユーザーをログアウト
        Auth::logout();

        // ログイン画面にリダイレクトし、ログアウト完了メッセージを表示
        return redirect('/login')->with('success', 'ログアウトしました。');
    }
}
