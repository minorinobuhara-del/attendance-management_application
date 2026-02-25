<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        // 登録後ユーザーはログイン状態なので /email/verify へ
        return redirect()->route('verification.notice');
    }
}