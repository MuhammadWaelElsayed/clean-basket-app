<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\StoreIntegrationTokenRequest;
use App\Models\IntegrationToken;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class IntegrationTokenController extends Controller
{
    public function store(StoreIntegrationTokenRequest $request): JsonResponse
    {
        // 1) تحقق من عدم تكرار (provider + name)
        $exists = IntegrationToken::where('provider', $request->provider)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Token with same provider & name already exists.'
            ], 422);
        }

        // 2) ولادة التوكن العشوائي (مرة واحدة للعرض)
        $rawToken  = bin2hex(random_bytes(48)); // ~96 hex chars
        $tokenHash = hash('sha256', $rawToken);

        // 3) إنشاء السجل (نخزن الهاش فقط)
        $rec = IntegrationToken::create([
            'name'        => $request->name,
            'provider'    => $request->provider,
            'token_hash'  => $tokenHash,
            'token_hint'  => substr($rawToken, -8),
            'scopes'      => $request->input('scopes', ['webhook:write']),
            'is_active'   => $request->boolean('is_active', true),
            'expires_at'  => $request->input('expires_at'),
        ]);

        // 4) إرجاع التوكن النصّي مرة واحدة + بيانات مساعدة
        return response()->json([
            'id'          => $rec->id,
            'provider'    => $rec->provider,
            'name'        => $rec->name,
            'token'       => $rawToken,             // ⚠️ لا يعرض لاحقًا
            'token_hint'  => $rec->token_hint,
            'scopes'      => $rec->scopes,
            'is_active'   => $rec->is_active,
            'expires_at'  => optional($rec->expires_at)->toISOString(),
            'created_at'  => $rec->created_at->toISOString(),
        ], 201);
    }
}
