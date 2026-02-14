<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Admin system routes
        if ($request->is('admin/*')) {
            return route('admin.login');
        }

        // Support system routes (if you have them)
        if ($request->is('support/*')) {
            return route('support.login');
        }

        // Default for regular users
        return route('admin.dashboard');
    }
}
