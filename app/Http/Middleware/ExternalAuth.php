<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class ExternalAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Missing token'], 401);
        }

        try {
            $secret = config('services.external_jwt.secret');
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            // تمهيد سياق الجهة الخارجية للـ Controllers/Policies
            $request->attributes->set('external_client_id', $decoded->sub ?? null);
            $request->attributes->set('external_number', $decoded->ext_no ?? null);

            // ضع أي تحقق إضافي هنا لاحقاً (مثلاً ربط vendor أو صلاحيات خاصة)
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }

        return $next($request);
    }
}
