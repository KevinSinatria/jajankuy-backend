<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!auth('api')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login, silahkan login terlebih dahulu.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
