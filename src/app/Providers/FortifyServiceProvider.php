<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\LoginRequest;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(fn () => view('auth.register'));

        Fortify::loginView(function () {
            return request()->is('admin/*')
                ? view('admin.auth.login')
                : view('auth.login');
        });

        Fortify::authenticateUsing(function (Request $request) {
            $form = new LoginRequest();

            Validator::make(
                $request->all(),
                $form->rules(),
                $form->messages()
            )->validate();

            $isAdmin = $request->is('admin/*');

            $account = ($isAdmin ? Admin::query() : User::query())
                ->where('email', $request->email)
                ->first();

            if ($account && Hash::check($request->password, $account->password)) {
                return $account;
            }

            throw ValidationException::withMessages([
                'email' => 'ログイン情報が登録されていません',
            ]);
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}