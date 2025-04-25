<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 1. メールアドレスが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_login_fails_if_email_is_missing()
    {
        // 1. ユーザーを事前に作成
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 2. メールアドレスを入力せず、パスワードを入力
        $formData = [
            'email' => '',
            'password' => 'password123',
        ];

        // 3. ログインのPOSTリクエスト
        $response = $this->post(route('login'), $formData);

        // 4. エラーメッセージを確認
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * ✅ 2. パスワードが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_login_fails_if_password_is_missing()
    {
        // 1. ユーザーを事前に作成
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 2. メールアドレスを入力し、パスワードを入力しない
        $formData = [
            'email' => 'test@example.com',
            'password' => '',
        ];

        // 3. ログインのPOSTリクエスト
        $response = $this->post(route('login'), $formData);

        // 4. エラーメッセージを確認
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * ✅ 3. 入力情報が間違っている場合、バリデーションメッセージが表示される
     */
    public function test_login_fails_if_credentials_are_wrong()
    {
        // 1. ユーザーを事前に作成
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 2. 存在しないユーザー情報を入力
        $formData = [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ];

        // 3. ログインのPOSTリクエスト
        $response = $this->post(route('login'), $formData);

        // 4. エラーメッセージを確認
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
