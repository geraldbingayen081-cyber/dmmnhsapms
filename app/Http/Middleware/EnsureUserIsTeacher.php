<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsTeacher
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->role !== 'teacher' && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access. Only faculty teachers or administrators can access the Teacher Portal.');
        }

        return $next($request);
    }
}
