<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWhatsappBotRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // logger('Request IP: ' . $request->ip());
        // return response()->json(['ip' => $request->ip()]);

        $allowedIps = [
            '127.0.0.1',
        ];

        $apiKey = $request->header('X-BOT-KEY');
        $expectedKey = config('services.bot.secret'); // from env

        // verify ip
        if (!in_array($request->ip(), $allowedIps)) {
            return response()->json(['message' => 'Unauthorized IP'], 403);
        }

        // verify api key
        if (!$apiKey || $apiKey !== $expectedKey) {
            return response()->json(['message' => 'Invalid API Key'], 401);
        }




        return $next($request);
    }
}
