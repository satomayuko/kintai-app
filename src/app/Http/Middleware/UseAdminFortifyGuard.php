<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UseAdminFortifyGuard
{
    public function handle($request, Closure $next)
    {
        config([
            'fortify.guard' => 'admin',
            'fortify.home'  => '/admin/dashboard',
        ]);

        Auth::shouldUse('admin');

        Log::debug('UseAdminFortifyGuard fired', [
            'method' => $request->method(),
            'path' => $request->path(),
            'fortify.guard' => config('fortify.guard'),
            'auth.default' => Auth::getDefaultDriver(),
            'admin.check.before' => Auth::guard('admin')->check(),
        ]);

        $response = $next($request);

        Log::debug('UseAdminFortifyGuard after', [
            'method' => $request->method(),
            'path' => $request->path(),
            'admin.check.after' => Auth::guard('admin')->check(),
        ]);

        return $response;
    }
}