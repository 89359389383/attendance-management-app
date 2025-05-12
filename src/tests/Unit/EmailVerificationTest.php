<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Support\Facades\URL;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ 1. 会員登録後、認証メールが送信されることを確認するテスト
     * 要件:
     * 1. 会員登録をする
     * 2. 認証メールを送信する → 登録したメールアドレス宛に認証メールが送信されている
     */
    public function test_verification_email_is_sent_after_registration()
    {
        // 【ステップ1】通知送信をフェイク（実際には送らない）
        Notification::fake();

        // 【ステップ2】会員登録データを送信
        $formData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        $this->post(route('register.store'), $formData);

        // 【ステップ3】登録されたユーザーが存在することを確認
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        // 【ステップ4】そのユーザーに対して認証メールが送信されたことを確認
        Notification::assertSentTo($user, CustomVerifyEmail::class);
    }

    /**
     * ✅ 2. メール認証導線画面に「認証はこちらから」ボタンが表示され、リンクが正しいことを確認するテスト
     * 要件:
     * 1. メール認証導線画面を表示する
     * 2. 「認証はこちらから」ボタンを押下
     * 3. メール認証サイトを表示する → メール認証サイトに遷移する
     */
    public function test_verification_button_redirects_to_mailhog()
    {
        // 【ステップ1】未認証状態のユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ])->first();

        // 【ステップ2】そのユーザーとしてメール認証導線画面にアクセス
        $response = $this->actingAs($user)->get(route('verification.notice'));

        // 【ステップ3】表示されたHTMLを取得
        $html = $response->getContent();

        // 【ステップ4】リンク先が MailHog であることを確認（例: http://localhost:8025）
        $this->assertStringContainsString('href="http://localhost:8025"', $html);

        // 【ステップ5】ボタンに表示されているテキストが「認証はこちらから」であることを確認
        $this->assertStringContainsString('認証はこちらから', $html);
    }

    /**
     * ✅ 3. メール認証を完了すると勤怠画面にリダイレクトされることを確認するテスト
     * 要件:
     * 1. メール認証を完了する
     * 2. 勤怠画面を表示する → 勤怠画面に遷移する
     */
    public function test_successful_email_verification_redirects_to_attendance_page()
    {
        // 【ステップ1】未認証ユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ])->first();

        // 【ステップ2】署名付き認証URLを生成（本番と同じURL構造）
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 【ステップ3】認証URLにアクセス（＝メール内のリンクをクリックした動作に相当）
        $response = $this->actingAs($user)->get($verificationUrl);

        // 【ステップ4】アクセス後に勤怠画面（/attendance）へリダイレクトされるかを確認
        $response->assertRedirect('/attendance');

        // 【ステップ5】ユーザーのメール認証日時が登録されたことを確認
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
