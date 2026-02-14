<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveWhatsappUser
{
   /**
     * احصل رقم الهاتف من الهيدر، وابحث عن المستخدم واربطه.
     */
    public function handle(Request $request, Closure $next)
    {
        // نفترض أنك ترسل رقم الهاتف في هيدر X-User-Phone
        $phone = $request->header('X-User-Phone');

        if (! $phone) {
            return response()->json(['message' => 'Missing phone header'], 400);
        }

        $user = User::where('phone', $phone)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // اربط المستخدم الحالي
        Auth::setUser($user);
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}
