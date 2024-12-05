<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Verification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // not yet used, this is for verification
        if (!auth()->check() || !auth()->user()->is_verified) {
            return redirect()->route('verification.notice');
        }
        return $next($request);
    }
}
