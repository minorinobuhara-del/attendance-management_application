<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            // 未入力
            'name.required' => 'お名前を入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',

            // パスワード規則
            'password.min' => 'パスワードは8文字以上で入力してください',

            // 確認用パスワード不一致（confirmed の文言を固定）
            'password.confirmed' => 'パスワードと一致しません',
        ];
    }
}