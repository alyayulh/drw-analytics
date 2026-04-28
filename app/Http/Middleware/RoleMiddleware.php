<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        if (auth()->user()->role !== $role) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}