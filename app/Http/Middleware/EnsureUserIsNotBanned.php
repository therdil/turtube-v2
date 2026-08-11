<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->is('api/*')
            ? $request->user('sanctum')
            : $request->user();

        if ($user?->banned_at) {
            if ($request->is('api/*')) {
                $user->currentAccessToken()?->delete();

                return response()->json([
                    'message' => 'Hesabınız platform kuralları nedeniyle askıya alındı.',
                ], 403);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Hesabınız platform kuralları nedeniyle askıya alındı.');
        }

        return $next($request);
    }
}
