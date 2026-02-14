<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2bItemPrice extends Model
{
    protected $fillable = [
        'item_id',
        'pricing_tier_id',
        'custom_price',
        'discount_percentage',
        'effective_from',
        'effective_until',
        'is_active',
    ];

    protected $casts = [
        'custom_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function pricingTier()
    {
        return $this->belongsTo(B2bPricingTier::class);
    }

    // Check if price is currently valid
    public function isValid()
    {
        if (!$this->is_active) return false;

        $now = now();

        if ($this->effective_from && $now->lt($this->effective_from)) {
            return false;
        }

        if ($this->effective_until && $now->gt($this->effective_until)) {
            return false;
        }

        return true;
    }

    // Calculate final price
    public function getFinalPrice()
    {
        if ($this->custom_price) {
            return $this->custom_price;
        }

        if ($this->discount_percentage && $this->item) {
            return $this->item->price * (1 - $this->discount_percentage / 100);
        }

        return $this->item->price ?? 0;
    }
}
