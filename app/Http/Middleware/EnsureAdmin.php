<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Auth::check()

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->is_admin) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Admin required.'], 403);
            }

            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        if (auth()->user()->status !== 'active') {
            auth()->logout();

            return redirect()->route('login')->with('error', 'Your admin account has been suspended.');
        }

        return $next($request);
    }
}
