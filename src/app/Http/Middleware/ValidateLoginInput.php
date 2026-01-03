<?php

namespace App\Http\Middleware;

use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\LoginRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ValidateLoginInput
{
    public function handle(Request $request, Closure $next)
    {
        $form = $request->is('admin/*') ? new AdminLoginRequest() : new LoginRequest();

        Validator::make(
            $request->all(),
            $form->rules(),
            $form->messages()
        )->validate();

        return $next($request);
    }
}