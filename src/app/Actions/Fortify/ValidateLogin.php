<?php

namespace App\Actions\Fortify;

use App\Http\Requests\LoginRequest;

class ValidateLogin
{
    public function handle($request, $next)
    {
        // FormRequest を実行（ここで日本語メッセージ）
        app(LoginRequest::class)->validateResolved();

        return $next($request);
    }
}