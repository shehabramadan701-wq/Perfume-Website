<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()?->is_admin) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
