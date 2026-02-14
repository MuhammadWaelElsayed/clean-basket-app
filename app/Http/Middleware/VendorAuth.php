<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Vendor;

class VendorAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // If the request expects JSON response, treat it as an API request
        if ($request->expectsJson()) {
            $vendor = Vendor::find(auth()->user()->id);
            if (!$vendor){
                return response()->json([
                    "status"=>false,
                    "message"=>"Unauthorized. Login as vendor",
                ],401);
            }
            return $next($request);
        }
        // Middleware for Web
        else{
            $partner = \Illuminate\Support\Facades\Cache::get('partner');
            if (!$partner && (!session('partner') || session('partner')==null)) {
                return redirect('partner/login');
            }

            // إذا كان هناك بيانات في Cache ولكن ليس في Session، نحدث Session
            if ($partner && !session('partner')) {
                session(['partner' => $partner]);
            }

            return $next($request);
        }


    }
}
