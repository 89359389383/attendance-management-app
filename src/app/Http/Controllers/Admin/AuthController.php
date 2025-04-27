<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // ← ログ用
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
        Log::info('【管理者ログイン画面】ログインフォーム表示リクエスト受信');

        return view('admin.auth.login');
    }

    /**
     * 管理者ログイン処理を行うメソッド
     * URL: /admin/login
     * メソッド: POST
     */
    public function login(LoginRequest $request)
    {
        Log::info('【管理者ログイン処理】ログインリクエスト受信', [
            'email' => $request->input('email')
        ]);

        $credentials = $request->only('email', 'password');

        Log::info('【管理者ログイン処理】認証情報作成完了', [
            'credentials' => [
                'email' => $credentials['email'],
            ],
        ]);

        // emailに該当するユーザーが存在するか調査（追加）
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        if (!$user) {
            // ① ユーザーが存在しない場合のログ
            Log::warning('【管理者ログイン処理】ユーザーが存在しない', [
                'email' => $credentials['email']
            ]);
        } else {
            // ② ユーザーは存在する場合 → さらに is_admin を確認
            Log::info('【管理者ログイン処理】ユーザー存在確認OK', [
                'email' => $credentials['email'],
                'is_admin' => $user->is_admin,
                'password_db_hash' => $user->password, // 保存されているパスワードハッシュも記録（機密ログ注意）
            ]);
        }

        // 通常ログイン試行
        if (Auth::attempt($credentials)) {
            Log::info('【管理者ログイン処理】ログイン成功（初回認証OK）', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name
            ]);

            // is_adminチェック
            if (Auth::user()->is_admin) {
                Log::info('【管理者ログイン処理】管理者権限確認OK', [
                    'user_id' => Auth::id(),
                ]);

                return redirect()->intended('/admin/attendance/list');
            } else {
                // 管理者でないなら即ログアウト
                Log::warning('【管理者ログイン処理】管理者権限なしのためログアウト', [
                    'user_id' => Auth::id(),
                ]);
                Auth::logout();
            }
        } else {
            // ③ ログイン試行失敗時（password mismatchの可能性）
            Log::warning('【管理者ログイン処理】パスワード不一致によるログイン失敗', [
                'email' => $credentials['email'],
                'input_password' => '[マスク済み]', // 本番環境では絶対平文パスワードを記録しない
            ]);
        }

        // ★ここに来たらすべて失敗
        Log::warning('【管理者ログイン処理】ログイン失敗（最終判定）', [
            'email' => $credentials['email']
        ]);

        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }

    /**
     * 管理者ログアウト処理を行うメソッド
     * URL: /logout（ヘッダーなどに POST リンクとして配置）
     * メソッド: POST
     */
    public function logout()
    {
        if (Auth::check()) {
            Log::info('【管理者ログアウト処理】ログアウト実行', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name
            ]);
        } else {
            Log::warning('【管理者ログアウト処理】未ログイン状態でのログアウトリクエスト受信');
        }

        Auth::logout();

        return redirect('/admin/login')->with('success', 'ログアウトしました。');
    }
}
