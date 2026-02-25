<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use App\Http\Responses\RegisterResponse;
use App\Http\Requests\LoginRequest;
use Laravel\Fortify\Contracts\FailedLoginResponse;
use App\Actions\Fortify\ValidateLogin;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    // ログイン画面差し替え
    Fortify::loginView(fn () => view('auth.login'));

    // 登録画面差し替え
    Fortify::registerView(fn () => view('auth.register'));


    //未認証ログインの制御
    Fortify::createUsersUsing(CreateNewUser::class);
    Fortify::authenticateUsing(function (Request $request) {

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return null;
    }

    // 未認証なら誘導（後述の verified 設定が前提）
    if (method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail()) {
        session(['needs_email_verification' => true]);
        return $user;
    }

    return $user;
});

    // 未認証なら誘導画面へ
    Fortify::authenticateThrough(function () {
        return [
        ValidateLogin::class, // 自作FormRequestバリデーション（日本語）
        \Laravel\Fortify\Actions\AttemptToAuthenticate::class,

        // 未認証なら誘導
        function ($request, $next) {
            if (session('needs_email_verification')) {
                Auth::logout();
                session()->forget('needs_email_verification');
                return redirect()->route('verification.notice');
            }
            return $next($request);
        },

        \Laravel\Fortify\Actions\PrepareAuthenticatedSession::class,
    ];
    });

    }
}
