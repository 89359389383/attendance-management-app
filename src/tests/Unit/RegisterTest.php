<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 1. 名前が必須項目であることを確認するテスト
     */
    public function test_name_is_required()
    {
        // 1. 名前を入力せずに、他の必要項目を入力
        $formData = [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 2. 登録ボタンを押す
        $response = $this->post(route('register.store'), $formData);

        // 3. 期待されるバリデーションメッセージを確認
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    /**
     * ✅ 2. メールアドレスが必須項目であることを確認するテスト
     */
    public function test_email_is_required()
    {
        // 1. メールアドレスを入力せずに、他の必要項目を入力
        $formData = [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 2. 登録ボタンを押す
        $response = $this->post(route('register.store'), $formData);

        // 3. 期待されるバリデーションメッセージを確認
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * ✅ 3. パスワードが必須項目であることを確認するテスト
     */
    public function test_password_is_required()
    {
        // 1. パスワードを入力せずに、他の必要項目を入力
        $formData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ];

        // 2. 登録ボタンを押す
        $response = $this->post(route('register.store'), $formData);

        // 3. 期待されるバリデーションメッセージを確認
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * ✅ 4. パスワードが8文字以上であることを確認するテスト
     */
    public function test_password_must_be_at_least_8_characters()
    {
        // 1. 7文字のパスワードを入力
        $formData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass123', // 7文字
            'password_confirmation' => 'pass123',
        ];

        // 2. 登録ボタンを押す
        $response = $this->post(route('register.store'), $formData);

        // 3. 期待されるバリデーションメッセージを確認
        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /**
     * ✅ 5. パスワードと確認用パスワードが一致することを確認するテスト
     */
    public function test_password_confirmation_must_match()
    {
        // 1. パスワードと確認用パスワードが異なる値を入力
        $formData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrongpassword',
        ];

        // 2. 登録ボタンを押す
        $response = $this->post(route('register.store'), $formData);

        // 3. 期待されるバリデーションメッセージを確認
        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    /**
     * ✅ 6. 会員登録成功時にログインページにリダイレクトされることを確認するテスト
     */
    public function test_successful_registration_redirects_to_login()
    {
        // 1. 正しい情報を入力
        $formData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 2. 登録ボタンを押す
        $response = $this->post(route('register.store'), $formData);

        // 3. メール認証画面へリダイレクトされることを確認
        $response->assertRedirect('/email/verify');

        // 4. ユーザーがDBに登録されていることを確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }
}
