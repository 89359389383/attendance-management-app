<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CustomVerifyEmail; // ←★カスタム通知に修正
use Illuminate\Support\Facades\URL;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. 会員登録後、認証メールが送信されることを確認するテスト
     */
    public function test_verification_email_is_sent_after_registration()
    {
        // 通知をフェイク
        Notification::fake();

        // 会員登録する
        $formData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        $this->post(route('register.store'), $formData);

        // 登録したユーザー取得
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        // 認証メールが送信されたかチェック（★カスタムクラス指定）
        Notification::assertSentTo($user, CustomVerifyEmail::class);
    }

    /**
     * 2. メール認証導線画面に「認証はこちらから」ボタンが表示され、リンクが正しいことを確認するテスト
     */
    public function test_verification_button_redirects_to_mailhog()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ])->first();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $html = $response->getContent();

        // ボタンのhrefが http://localhost:8025/ を指していることを確認
        $this->assertStringContainsString('href="http://localhost:8025"', $html);

        // ボタンのテキストも確認（認証はこちらから）
        $this->assertStringContainsString('認証はこちらから', $html);
    }

    /**
     * 3. メール認証を完了すると勤怠画面にリダイレクトされることを確認するテスト
     */
    public function test_successful_email_verification_redirects_to_attendance_page()
    {
        // 未認証のユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ])->first();

        // 認証用の署名付きURLを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 認証リンクにアクセス
        $response = $this->actingAs($user)->get($verificationUrl);

        // 勤怠画面にリダイレクトされるかチェック
        $response->assertRedirect('/attendance');

        // メール認証日時が登録されたかチェック
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
