<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
{
    // 如果 Header 符合就過關，否則回傳 401
    if ($request->header('Authorization') === 'Bearer admin-secret-token') {
        return $next($request);
    }
    return response()->json(['message' => '未經授權'], 401);
}
}
