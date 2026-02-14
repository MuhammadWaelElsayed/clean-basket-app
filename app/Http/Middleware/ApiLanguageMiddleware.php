<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ApiLanguageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Set the application locale based on the language parameter
        if (isset( $request->language) &&  $request->language == 'ar') {
            App::setLocale('ar');
        } 
        elseif(isset( $request->language) &&  $request->language == 'en'){
            App::setLocale('en');
        }
        elseif($request->expectsJson() ) {
            App::setLocale('en');
        }

        return $next($request);
    }
}
