<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminAuth
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
//        $admin = Cache::get('admin');
//
//        // إذا لم يكن هناك admin في Cache، تحقق من Session
//        if (!$admin && session('admin')) {
//            $admin = session('admin');
//            // أعد حفظه في Cache
//            Cache::put('admin', $admin, now()->addHours(24));
//        }
//
//        if (!$admin) {
//            $request->session()->forget('admin');
//            return redirect('admin/login');
//        } else {
//            session(['admin' => $admin]);
//        }

//        if (!auth('admin')->check()) {
//            return redirect()->route('admin.login');
//        }

        return $next($request);
    }
}
