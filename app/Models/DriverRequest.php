<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    protected $appends = ['is_expired'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function getIsExpiredAttribute()
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast() && $this->status === 'PENDING';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING')
            ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'PENDING')
            ->where('expires_at', '<=', now());
    }

    // Scope for pickup requests
    public function scopePickup($query)
    {
        return $query->where('request_type', 'PICKUP');
    }

    // Scope for delivery requests
    public function scopeDelivery($query)
    {
        return $query->where('request_type', 'DELIVERY');
    }

    public function markAsExpired()
    {
        return $this->update([
            'status' => 'EXPIRED',
            'responded_at' => now(),
        ]);
    }

    public function accept()
    {
        return $this->update([
            'status' => 'ACCEPTED',
            'responded_at' => now(),
        ]);
    }

    public function reject($reason = null)
    {
        return $this->update([
            'status' => 'REJECTED',
            'responded_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    // Check if this is a pickup request
    public function isPickup()
    {
        return $this->request_type === 'PICKUP';
    }

    // Check if this is a delivery request
    public function isDelivery()
    {
        return $this->request_type === 'DELIVERY';
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'ACCEPTED');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }

}
