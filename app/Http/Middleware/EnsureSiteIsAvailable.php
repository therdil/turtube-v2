<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteIsAvailable
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->is('admin/*')
            || $request->is('up')
            || $request->is('login')
            || $request->is('forgot-password')
            || $request->is('reset-password/*')
            || $request->user()?->is_admin
        ) {
            return $next($request);
        }

        $settings = SiteSetting::current();

        if ($settings->maintenance_mode) {
            return response()->view('errors.maintenance', ['settings' => $settings], 503);
        }

        return $next($request);
    }
}
