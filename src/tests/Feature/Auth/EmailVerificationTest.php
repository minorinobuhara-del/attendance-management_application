<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class EmailVerificationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test
     * 会員登録後、認証メールが送信される
     */
    public function verification_email_is_sent_after_registration()
    {
        Notification::fake();

        $this->post('/register', [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test
     * メール認証誘導画面で「認証はこちらから」ボタンが表示される
     */
    public function verification_notice_page_is_displayed()
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertSee('認証はこちらから');
    }

    /** @test
     * メール認証を完了すると、勤怠登録画面に遷移する
     */
    public function user_is_redirected_to_attendance_page_after_email_verification()
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/attendance?verified=1');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
