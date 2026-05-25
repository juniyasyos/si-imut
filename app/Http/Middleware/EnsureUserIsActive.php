<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request):Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // if ($user && in_array($user->status, ['inactive', 'suspended'])) {
        //     abort(403, 'Akun Anda tidak aktif atau sedang ditangguhkan.');
        // }
        
        if ($user && !$user->active) {
            abort(403, 'Akun Anda tidak aktif atau sedang ditangguhkan.');
        }

        return $next($request);
    }
}
