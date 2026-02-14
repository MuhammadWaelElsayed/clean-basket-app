<?php

namespace App\Listeners;

use App\Events\DriverLocationBroadcast;
use App\Models\Driver;
use Illuminate\Support\Facades\Log;
use Laravel\Reverb\Events\MessageReceived;

class ProcessClientEvent
{
    public function handle(MessageReceived $event): void
    {
        $message = json_decode($event->message, true);

        // Handle driver location update
        if ($message['event'] === 'client-driver-location-update') {
            $this->handleDriverLocationUpdate($message);
        }
    }

    private function handleDriverLocationUpdate($message): void
    {
        try {
            $data = json_decode($message['data'], true);

            Log::info('Processing driver location update');

            if (!isset($data['driver_id'], $data['latitude'], $data['longitude'])) {
                Log::error('Invalid location data');
                return;
            }

            // Update driver in database
            $driver = Driver::find($data['driver_id']);

            if (!$driver) {
                Log::error('Driver not found', ['driver_id' => $data['driver_id']]);
                return;
            }

            $driver->update([
                'lat' => $data['latitude'],
                'lng' => $data['longitude'],
//                'last_location_update' => now(),
            ]);

            Log::info('Driver location updated in database', [
                'driver_id' => $driver->id,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
            ]);

//            // Broadcast to tracking channel for customers/admins
//            broadcast(new DriverLocationBroadcast(
//                driverId: $driver->id,
//                latitude: $data['latitude'],
//                longitude: $data['longitude'],
//                heading: $data['heading'] ?? null,
//                speed: $data['speed'] ?? null
//            ));
//
//            Log::info('Location broadcast sent to tracking channel');

        } catch (\Throwable $e) {
            Log::error('Error processing driver location', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
