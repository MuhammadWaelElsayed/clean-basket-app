<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private channel for driver location updates
Broadcast::channel('private-driver-location.{driverId}', function ($user, $driverId) {
    // Verify the authenticated user is the driver
    return (int) $user->id === (int) $driverId;
});

Broadcast::channel('public-driver-location', function ($user, $driverId) {
    // Verify the authenticated user is the driver
    return true;
});

// Public channel for listening to driver location (for customers/admins)
Broadcast::channel('driver-tracking.{driverId}', function ($user, $driverId) {
    // Anyone authenticated can listen
    return true;
});
