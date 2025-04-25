<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 1. メールアドレスが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_fails_if_email_is_missing()
    {
        // 1. 管理者ユーザーを作成
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass'),
            'is_admin' => true,
        ]);

        // 2. メールアドレスを空にしてログイン試行
        $formData = [
            'email' => '',
            'password' => 'adminpass',
        ];

        // 3. 管理者ログインのPOSTリクエスト
        $response = $this->post(route('admin.login'), $formData);

        // 4. 期待されるバリデーションメッセージ
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * ✅ 2. パスワードが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_fails_if_password_is_missing()
    {
        // 1. 管理者ユーザーを作成
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass'),
            'is_admin' => true,
        ]);

        // 2. パスワードを空にしてログイン試行
        $formData = [
            'email' => 'admin@example.com',
            'password' => '',
        ];

        // 3. 管理者ログインのPOSTリクエスト
        $response = $this->post(route('admin.login'), $formData);

        // 4. 期待されるバリデーションメッセージ
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * ✅ 3. ログイン情報が一致しない場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_fails_if_credentials_are_wrong()
    {
        // 1. 管理者ユーザーを作成
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass'),
            'is_admin' => true,
        ]);

        // 2. 間違った情報でログイン試行
        $formData = [
            'email' => 'wrong@example.com',
            'password' => 'wrongpass',
        ];

        // 3. 管理者ログインのPOSTリクエスト
        $response = $this->post(route('admin.login'), $formData);

        // 4. エラーメッセージを確認
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
