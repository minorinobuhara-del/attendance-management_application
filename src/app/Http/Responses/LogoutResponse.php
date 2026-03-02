<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        // admin配下からのログアウトなら管理者ログインへ
        if ($request->is('admin/*')) {
            return redirect()->route('admin.login');
        }

        // それ以外は一般ユーザーのログインへ
        return redirect()->route('login');
    }
}