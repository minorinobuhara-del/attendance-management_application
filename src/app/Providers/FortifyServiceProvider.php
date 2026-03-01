<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ValidateLogin;
use App\Http\Responses\RegisterResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\FailedLoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use App\Http\Responses\LogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 登録後の遷移（メール認証誘導へ）
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);

        // ログイン失敗時の固定文言
        $this->app->singleton(FailedLoginResponse::class, function () {
            return new class implements FailedLoginResponse {
                public function toResponse($request)
                {
                    return back()
                        ->withErrors(['email' => 'ログイン情報が登録されていません'])
                        ->onlyInput('email');
                }
            };
        });

        //ログアウト後の遷移
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
    }

    public function boot(): void
    {
        // 画面差し替え（/admin は管理者用に）
        Fortify::loginView(function () {
    if (request()->is('admin/*')) {
        return view('admin.login');   // ← 管理者ログインBlade
    }
    return view('auth.login');            // ← 一般ユーザーログインBlade
    });

        Fortify::registerView(function () {
        // 管理者側に登録画面が不要なら admin は一般側に行かせない（一般用のみ）
        return view('auth.register');
    });

        // 登録処理
        Fortify::createUsersUsing(CreateNewUser::class);

        // 認証判定（パスワード一致の確認＋未認証フラグ）
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return null; // FailedLoginResponse の固定文言へ
            }

            // 未認証なら誘導用フラグ
            if (method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail()) {
                session(['needs_email_verification' => true]);
            }

            return $user;
        });

        // ログインの流れ（英語requiredを潰す：FormRequestを先頭で実行）
        Fortify::authenticateThrough(function () {
            return [
                ValidateLogin::class,        // FormRequestで日本語必須チェック

                AttemptToAuthenticate::class, // 認証試行

                // 未認証ならメール認証誘導画面へ
                function ($request, $next) {
                    if (session('needs_email_verification')) {
                        Auth::logout();
                        session()->forget('needs_email_verification');
                        return redirect()->route('verification.notice');
                    }
                    return $next($request);
                },

                PrepareAuthenticatedSession::class, // セッション準備（最後）
            ];
        });

        RateLimiter::for('login', function (Request $request) {
        return Limit::perMinute(100)->by($request->ip());
        });
    }
}