<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperadmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! Auth::user()->isSuperadmin()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'SUPERADMIN_FORBIDDEN',
                        'message' => 'Akses disekat. Hanya Superadmin dibenarkan memasuki konsol ini.',
                    ],
                ], 403);
            }

            abort(403, 'Akses Pusat Kawalan Superadmin disekat. Hanya pemilik self-hosted dibenarkan.');
        }

        return $next($request);
    }
}
