<?php

namespace Modules\Shared\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->roles()->where('slug', 'admin')->exists()) {
            abort(403, 'Fitur ini hanya untuk administrator.');
        }

        return $next($request);
    }
}
