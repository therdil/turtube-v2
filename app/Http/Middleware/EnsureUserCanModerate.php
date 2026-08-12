<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanModerate
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->is('api/*')
            ? $request->user('sanctum')
            : $request->user('web');

        abort_unless($user?->is_admin || $user?->is_moderator, 403);

        return $next($request);
    }
}
