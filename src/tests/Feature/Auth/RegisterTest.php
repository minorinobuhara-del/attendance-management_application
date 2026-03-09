<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test
     * 名前が未入力の場合、バリデーションメッセージが表示される
     */
    public function name_is_required()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    /** @test
     *メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function email_is_required()
    {
        $response = $this->post('/register', [
            'name' => '山田太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** @test
     * パスワードが8文字未満の場合、バリデーションメッセージが表示される
     */
    public function password_must_be_at_least_8_characters()
    {
        $response = $this->post('/register', [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'pass12',
            'password_confirmation' => 'pass12',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /** @test
     * パスワードが一致しない場合、バリデーションメッセージが表示される
     */
    public function password_confirmation_must_match()
    {
        $response = $this->post('/register', [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password999',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    /** @test
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function password_is_required()
    {
        $response = $this->post('/register', [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** @test
     * フォームに内容が入力されていた場合、データが正常に保存される
     */
    public function user_can_register_with_valid_data()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', [
            'name' => '山田太郎',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

}
