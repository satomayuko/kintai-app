<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetFortifyAdminGuard
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('admin', 'admin/*')) {
            config([
                'fortify.guard' => 'admin',
                'fortify.passwords' => 'admins',
                'fortify.home' => '/admin/attendance/list',
            ]);
        }

        return $next($request);
    }
}