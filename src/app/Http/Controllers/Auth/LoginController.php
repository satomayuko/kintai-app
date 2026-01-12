<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function store(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        if ($request->is('admin') || $request->is('admin/*')) {
            if (!Auth::guard('admin')->attempt($credentials, false)) {
                throw ValidationException::withMessages([
                    'email' => 'ログイン情報が登録されていません',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended(route('admin.attendance.list'));
        }

        if (!Auth::attempt($credentials, false)) {
            throw ValidationException::withMessages([
                'email' => 'ログイン情報が登録されていません',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('attendance.index'));
    }
}