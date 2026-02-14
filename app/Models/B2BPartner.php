<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class B2BPartner extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'b2b_partners';

    protected $guarded = [];

    protected $casts = [
        'service_fees' => 'decimal:2',
        'delivery_fees' => 'decimal:2',
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all secrets for this partner
     */
    public function secrets()
    {
        return $this->hasMany(B2BPartnerSecret::class, 'b2b_partner_id');
    }

    /**
     * Get the active secret for this partner
     */
    public function activeSecret()
    {
        return $this->hasOne(B2BPartnerSecret::class, 'b2b_partner_id')
            ->where('active', true)
            ->latest();
    }

    /**
     * Get the current active secret value
     */
    public function getActiveSecretAttribute()
    {
        return $this->activeSecret?->secret;
    }

    /**
     * Scope to get only active partners
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Check if partner has an active secret
     */
    public function hasActiveSecret(): bool
    {
        return $this->secrets()->where('active', true)->exists();
    }
}
