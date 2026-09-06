<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsParent
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->role !== 'parent' && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access. Only registered parents/guardians or administrators can access the Parent Portal.');
        }

        return $next($request);
    }
}
