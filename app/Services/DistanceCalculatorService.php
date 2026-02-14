<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Trip;

class DistanceCalculatorService
{
    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in kilometers
     *
     * @param float $lat1 Latitude of point 1
     * @param float $lng1 Longitude of point 1
     * @param float $lat2 Latitude of point 2
     * @param float $lng2 Longitude of point 2
     * @return float Distance in kilometers
     */
    public function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        // Validate coordinates
        if (!$this->isValidCoordinate($lat1, $lng1) || !$this->isValidCoordinate($lat2, $lng2)) {
            return 0;
        }

        $earthRadius = 6371; // Earth's radius in kilometers

        // Convert degrees to radians
        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);

        // Haversine formula
        $latDelta = $lat2 - $lat1;
        $lngDelta = $lng2 - $lng1;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($lat1) * cos($lat2) *
            sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    /**
     * Calculate distance for a pickup trip
     * Route: Driver location → Customer location → Vendor location
     *
     * @param Trip $trip
     * @param Order $order
     * @return float Distance in kilometers
     */
    public function calculatePickupTripDistance(Trip $trip, Order $order)
    {
        // For pickup: start location → customer → vendor
        $startLat = $trip->start_lat;
        $startLng = $trip->start_lng;
        $endLat = $trip->end_lat ?? $order->vendor->lat ?? null;
        $endLng = $trip->end_lng ?? $order->vendor->lng ?? null;

        if (!$startLat || !$startLng) {
            // If no start location, calculate from customer to vendor
            $startLat = $order->user->lat;
            $startLng = $order->user->lng;
        }

        if (!$endLat || !$endLng) {
            return 0;
        }

        return $this->calculateDistance($startLat, $startLng, $endLat, $endLng);
    }

    /**
     * Calculate distance for a delivery trip
     * Route: Vendor location → Customer location
     *
     * @param Trip $trip
     * @param Order $order
     * @return float Distance in kilometers
     */
    public function calculateDeliveryTripDistance(Trip $trip, Order $order)
    {
        // For delivery: vendor → customer
        $startLat = $trip->start_lat ?? $order->vendor->lat ?? null;
        $startLng = $trip->start_lng ?? $order->vendor->lng ?? null;
        $endLat = $trip->end_lat ?? $order->user->lat ?? null;
        $endLng = $trip->end_lng ?? $order->user->lng ?? null;

        if (!$startLat || !$startLng || !$endLat || !$endLng) {
            return 0;
        }

        return $this->calculateDistance($startLat, $startLng, $endLat, $endLng);
    }

    /**
     * Calculate distance for a trip based on its type
     *
     * @param Trip $trip
     * @param Order $order
     * @return float Distance in kilometers
     */
    public function calculateTripDistance(Trip $trip, Order $order)
    {
        if ($trip->type === 'pickup') {
            return $this->calculatePickupTripDistance($trip, $order);
        } else {
            return $this->calculateDeliveryTripDistance($trip, $order);
        }
    }

    /**
     * Get estimated distance for a new pickup trip
     * Calculates from driver's current location to customer, then to vendor
     *
     * @param float $driverLat Driver's current latitude
     * @param float $driverLng Driver's current longitude
     * @param Order $order
     * @return array ['to_customer' => float, 'to_vendor' => float, 'total' => float]
     */
    public function estimatePickupDistance($driverLat, $driverLng, Order $order)
    {
        $toCustomer = $this->calculateDistance(
            $driverLat,
            $driverLng,
            $order->user->lat,
            $order->user->lng
        );

        $toVendor = $this->calculateDistance(
            $order->user->lat,
            $order->user->lng,
            $order->vendor->lat ?? 0,
            $order->vendor->lng ?? 0
        );

        return [
            'to_customer' => $toCustomer,
            'to_vendor' => $toVendor,
            'total' => round($toCustomer + $toVendor, 2)
        ];
    }

    /**
     * Get estimated distance for a new delivery trip
     * Calculates from driver's current location to vendor, then to customer
     *
     * @param float $driverLat Driver's current latitude
     * @param float $driverLng Driver's current longitude
     * @param Order $order
     * @return array ['to_vendor' => float, 'to_customer' => float, 'total' => float]
     */
    public function estimateDeliveryDistance($driverLat, $driverLng, Order $order)
    {
        $toVendor = $this->calculateDistance(
            $driverLat,
            $driverLng,
            $order->vendor->lat ?? 0,
            $order->vendor->lng ?? 0
        );

        $toCustomer = $this->calculateDistance(
            $order->vendor->lat ?? 0,
            $order->vendor->lng ?? 0,
            $order->user->lat,
            $order->user->lng
        );

        return [
            'to_vendor' => $toVendor,
            'to_customer' => $toCustomer,
            'total' => round($toVendor + $toCustomer, 2)
        ];
    }

    /**
     * Calculate total distance traveled by a driver for an order
     * Sums up all completed trips for the order
     *
     * @param int $orderId
     * @param int $driverId
     * @return float Total distance in kilometers
     */
    public function calculateTotalOrderDistance($orderId, $driverId = null)
    {
        $query = Trip::where('order_id', $orderId)
            ->where('status', 'completed');

        if ($driverId) {
            $query->where('driver_id', $driverId);
        }

        return $query->sum('distance_km') ?? 0;
    }

    /**
     * Calculate driver's total distance for a time period
     *
     * @param int $driverId
     * @param string $startDate
     * @param string $endDate
     * @return float Total distance in kilometers
     */
    public function calculateDriverDistance($driverId, $startDate = null, $endDate = null)
    {
        $query = Trip::where('driver_id', $driverId)
            ->where('status', 'completed');

        if ($startDate) {
            $query->where('completed_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('completed_at', '<=', $endDate);
        }

        return $query->sum('distance_km') ?? 0;
    }

    /**
     * Get distance breakdown by trip type for a driver
     *
     * @param int $driverId
     * @param string $startDate
     * @param string $endDate
     * @return array ['pickup' => float, 'delivery' => float, 'total' => float]
     */
    public function getDriverDistanceBreakdown($driverId, $startDate = null, $endDate = null)
    {
        $query = Trip::where('driver_id', $driverId)
            ->where('status', 'completed');

        if ($startDate) {
            $query->where('completed_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('completed_at', '<=', $endDate);
        }

        $pickupDistance = (clone $query)->where('type', 'pickup')->sum('distance_km') ?? 0;
        $deliveryDistance = (clone $query)->where('type', 'delivery')->sum('distance_km') ?? 0;

        return [
            'pickup' => round($pickupDistance, 2),
            'delivery' => round($deliveryDistance, 2),
            'total' => round($pickupDistance + $deliveryDistance, 2)
        ];
    }

    /**
     * Validate if coordinates are valid
     *
     * @param float $lat
     * @param float $lng
     * @return bool
     */
    private function isValidCoordinate($lat, $lng)
    {
        if (!$lat || !$lng) {
            return false;
        }

        // Check if latitude is between -90 and 90
        if ($lat < -90 || $lat > 90) {
            return false;
        }

        // Check if longitude is between -180 and 180
        if ($lng < -180 || $lng > 180) {
            return false;
        }

        return true;
    }

    /**
     * Convert distance from kilometers to miles
     *
     * @param float $km
     * @return float
     */
    public function kmToMiles($km)
    {
        return round($km * 0.621371, 2);
    }

    /**
     * Convert distance from miles to kilometers
     *
     * @param float $miles
     * @return float
     */
    public function milesToKm($miles)
    {
        return round($miles / 0.621371, 2);
    }

    /**
     * Calculate ETA (Estimated Time of Arrival) based on distance
     * Assumes average speed of 40 km/h in urban areas
     *
     * @param float $distanceKm
     * @param float $avgSpeed Average speed in km/h (default: 40)
     * @return int ETA in minutes
     */
    public function calculateETA($distanceKm, $avgSpeed = 40)
    {
        if ($distanceKm <= 0 || $avgSpeed <= 0) {
            return 0;
        }

        $hours = $distanceKm / $avgSpeed;
        $minutes = $hours * 60;

        return (int) ceil($minutes);
    }

    /**
     * Get human-readable distance
     *
     * @param float $distanceKm
     * @return string
     */
    public function formatDistance($distanceKm)
    {
        if ($distanceKm < 1) {
            return round($distanceKm * 1000) . ' m';
        }

        return round($distanceKm, 2) . ' km';
    }

    /**
     * Calculate cost based on distance
     *
     * @param float $distanceKm
     * @param float $baseRate Base rate per km
     * @param float $minimumCharge Minimum charge
     * @return float
     */
    public function calculateDistanceCost($distanceKm, $baseRate = 2.5, $minimumCharge = 5)
    {
        $cost = $distanceKm * $baseRate;

        return max($cost, $minimumCharge);
    }
}
