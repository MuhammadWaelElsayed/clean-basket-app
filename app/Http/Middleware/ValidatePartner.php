<?php

namespace App\Http\Middleware;

use App\Models\B2BPartner;
use App\Models\B2BPartnerSecret;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidatePartner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $source = $request->header('X-Source');
        $long = $request->header('X-Lon');
        $lat = $request->header('X-Lat');

        // Check if required headers are present
        if (!$source) {
            return response()->json([
                'status' => false,
                'message' => 'Missing required headers (X-Source)'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$long) {
            return response()->json([
                'status' => false,
                'message' => 'Missing required headers (X-Lon)'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$lat) {
            return response()->json([
                'status' => false,
                'message' => 'Missing required headers (X-Lat)'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate source and secret from database
        $partner = $this->validatePartnerSecret($source);

        if (!$partner) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid source credentials or inactive source'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Prepare source data for backward compatibility
        $sourceData = [
            'id' => $partner->id,
            'name' => $partner->name,
            'secret' => $source,
            'service_fees' => (float) $partner->service_fees,
            'delivery_fees' => (float) $partner->delivery_fees,
            'active' => $partner->active,
        ];

        // Add source data to request for later use in controllers
        $request->attributes->set('source', $sourceData);
        $request->attributes->set('partner', $partner);
        $request->attributes->set('partner_id', $partner->id);

        return $next($request);
    }

    /**
     * Validate partner secret and return partner if valid
     *
     * @param string $secret
     * @return B2BPartner|null
     */
    private function validatePartnerSecret(string $secret): ?B2BPartner
    {
        // Find active secret
        $partnerSecret = B2BPartnerSecret::where('secret', $secret)
            ->where('active', true)
            ->first();

        if (!$partnerSecret) {
            return null;
        }

        // Get partner and check if active
        $partner = B2BPartner::where('id', $partnerSecret->b2b_partner_id)
            ->where('active', true)
            ->first();

        return $partner;
    }

    /**
     * Get partner by secret (for backward compatibility)
     *
     * @param string $sourceSecret
     * @return array|null
     */
    public static function getSource(string $sourceSecret): ?array
    {
        // Find active secret
        $partnerSecret = B2BPartnerSecret::where('secret', $sourceSecret)
            ->where('active', true)
            ->first();

        if (!$partnerSecret) {
            return null;
        }

        // Get partner
        $partner = B2BPartner::where('id', $partnerSecret->b2b_partner_id)
            ->where('active', true)
            ->first();

        if (!$partner) {
            return null;
        }

        return [
            'id' => $partner->id,
            'name' => $partner->name,
            'secret' => $sourceSecret,
            'service_fees' => (float) $partner->service_fees,
            'delivery_fees' => (float) $partner->delivery_fees,
            'active' => $partner->active,
        ];
    }

    /**
     * Get partner model by secret
     *
     * @param string $sourceSecret
     * @return B2BPartner|null
     */
    public static function getPartner(string $sourceSecret): ?B2BPartner
    {
        $partnerSecret = B2BPartnerSecret::where('secret', $sourceSecret)
            ->where('active', true)
            ->first();

        if (!$partnerSecret) {
            return null;
        }

        return B2BPartner::where('id', $partnerSecret->b2b_partner_id)
            ->where('active', true)
            ->first();
    }
}
