<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class OsrmService
{
    protected string $baseUrl;

    public function __construct()
    {
        // Use your own OSRM server or the public demo (rate-limited)
        $this->baseUrl = config('services.osrm.url', 'https://router.project-osrm.org');
    }

    /**
     * Get optimized trip for multiple orders (TSP solver)
     *
     * @param array $locations   [['lon' => 13.388, 'lat' => 52.517], ...]
     * @param bool  $roundtrip   Return to start? (true for delivery drivers)
     * @param array $options     Extra OSRM options
     *
     * @return array             Optimized route + waypoints order
     */
    public function getOptimizedTrip(array $locations, bool $roundtrip = true, array $options = []): array
    {
        if (count($locations) < 2) {
            throw new InvalidArgumentException('At least 2 locations required (start + 1 order).');
        }

        // First location is usually the driver's current position
        $coordinates = collect($locations)->map(fn($loc) => "{$loc['lon']},{$loc['lat']}")->implode(';');

        $query = array_merge([
            'source' => 'first',
            'destination' => 'last',
            'roundtrip' => $roundtrip ? 'true' : 'false',
            'overview' => 'false',
            'steps' => 'true',
            'geometries' => 'geojson',
            'annotations' => 'true',
        ], $options);

        $response = Http::get("{$this->baseUrl}/trip/v1/driving/{$coordinates}", $query);

        if ($response->failed() || $response->json('code') !== 'Ok') {
            throw new \Exception('OSRM Trip failed: ' . ($response->json('message') ?? 'Unknown error'));
        }

        return $response->json();
    }

    /**
     * Helper: Extract optimized order of locations (by original index)
     */
    public function getOptimizedOrder(array $tripResponse): array
    {
        return collect($tripResponse['waypoints'])
            ->sortBy('waypoint_index')
            ->pluck('trips_index') // this is the optimized sequence (0-based index of original locations)
            ->values()
            ->toArray();
    }

    /**
     * Get full route geometry + legs
     */
    public function getRouteGeometry(array $tripResponse): array
    {
        return $tripResponse['trips'][0]['geometry'] ?? [];
    }
}
