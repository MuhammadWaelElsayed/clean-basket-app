<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'status_code' => 'integer',
        'duration_ms' => 'integer',
        'attempt' => 'integer',
    ];

    /**
     * Get the partner webhook that owns this log.
     */
    public function partnerWebhook()
    {
        return $this->belongsTo(PartnerWebhook::class);
    }

    /**
     * Scope to get successful webhooks.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope to get failed webhooks.
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'permanently_failed']);
    }

    /**
     * Scope to get webhooks by event type.
     */
    public function scopeByEvent($query, string $event)
    {
        return $query->where('event', $event);
    }
}
