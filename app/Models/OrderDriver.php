<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDriver extends Model
{
    protected $table = 'order_driver';

    protected $fillable = [
        'external_ride_id',
        'order_id',
        'vendor_id',
        'driver_id',
        'start_lat',
        'start_lng',
        'end_lat',
        'end_lng',
        'trip_cost',
        'provider',
        'status',
        'time_changed',
    ];

    protected $casts = [
        'start_lat'   => 'float',
        'start_lng'   => 'float',
        'end_lat'     => 'float',
        'end_lng'     => 'float',
        'trip_cost'   => 'float',
        'time_changed'=> 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(ExternalDriver::class, 'driver_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    /**
     * Scope to filter by provider
     */
    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope to filter by Leajlak provider
     */
    public function scopeLeajlak($query)
    {
        return $query->where('provider', 'leajlak');
    }
}
