<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Pastikan pengguna terautentikasi dan memiliki hak akses admin (SUPER_ADMIN atau RACE_ADMIN).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isRaceAdmin()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses panel administrasi.');
        }

        return $next($request);
    }
}
