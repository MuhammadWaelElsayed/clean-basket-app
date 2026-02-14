<?php

namespace App\Http\Middleware;

use App\Models\IntegrationToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyIntegrationToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $auth = $request->header('Authorization');
        if (!$auth || !preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $rawToken  = trim($m[1]);
        $tokenHash = hash('sha256', $rawToken);

        $rec = IntegrationToken::query()
            ->where('token_hash', $tokenHash)
            ->where('is_active', true)
            ->whereNull('revoked_at')
            ->when(true, function ($q) {
                // صلاحية اختيارية
                $q->where(function ($q2) {
                    $q2->whereNull('expires_at')
                       ->orWhere('expires_at', '>', now());
                });
            })
            ->first();

        if (!$rec) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // (اختياري) زيادة العدّاد وتحديث آخر استخدام
        $rec->increment('use_count');
        $rec->forceFill(['last_used_at' => now()])->saveQuietly();

        // مرّر provider من التكامل (مفيد لو endpoint عام)
        if ($rec->provider && !$request->route('provider')) {
            $request->attributes->set('provider', $rec->provider);
        }

        return $next($request);
    }
}
