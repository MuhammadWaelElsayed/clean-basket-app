<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ExternalClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;

class ExternalAuthController extends Controller
{
    public function issueToken(Request $request)
    {
        $data = $request->validate([
            'client_id'     => 'required|string',
            'client_secret' => 'required|string',
        ]);

        $client = ExternalClient::where('client_id', $data['client_id'])->first();
        if (!$client || !$client->is_active || !Hash::check($data['client_secret'], $client->client_secret_hash)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $ttlMinutes = $client->token_ttl ?: 30;
        $now = time();
        $payload = [
            // 'iss' => config('app.url'),
            'sub' => $client->client_id,
            'ext_no' => $client->external_number,
            'iat' => $now,
            'exp' => $now + ($ttlMinutes * 60),
            // يمكنك إضافة claims أخرى بسيطة هنا عند الحاجة لاحقاً
            'jti' => (string) Str::uuid(),
            'name' => $client->name,
            'client_id' => $client->client_id,
        ];

        $secret = config('services.external_jwt.secret'); // اجعلها في config/services.php
        $jwt = JWT::encode($payload, $secret, 'HS256');

        return response()->json([
            'access_token' => $jwt,
            'token_type'   => 'Bearer',
            'expires_in'   => $ttlMinutes * 60,
        ]);
    }
}
