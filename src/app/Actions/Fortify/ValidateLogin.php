<?php

namespace App\Actions\Fortify;

use App\Http\Requests\LoginRequest;

class ValidateLogin
{
    public function handle($request, $next)
    {
        // ここで日本語メッセージのバリデーションを実行
        app(LoginRequest::class)->validateResolved();

        return $next($request);
    }
}