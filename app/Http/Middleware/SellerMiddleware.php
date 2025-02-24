<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SellerMiddleware
{
  public function handle(Request $request, Closure $next)
  {
    if (!auth()->check() || !auth()->user()->is_seller) {
      return redirect()->back()->with('error', 'You must be a seller to access this area.');
    }

    return $next($request);
  }
}
