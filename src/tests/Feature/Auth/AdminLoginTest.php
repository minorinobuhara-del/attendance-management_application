<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\Admin;

class AdminLoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /**
     *@test
     *メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required_for_admin_login()
    {
        Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /**
     *@test
     *パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required_for_admin_login()
    {
        Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /**
     *@test
     *登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_fails_with_invalid_credentials()
    {
        Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);

        $this->assertGuest('admin');
    }


}
