<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2bPricingTier extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'discount_percentage',
        'priority',
        'is_active',
        'min',
        'max',
        'type'
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Clients in this tier
    public function clients()
    {
        return $this->hasMany(B2bClient::class, 'pricing_tier_id');
    }

    // Custom item prices for this tier
    public function itemPrices()
    {
        return $this->hasMany(B2bItemPrice::class, 'pricing_tier_id');
    }

    // Get price for a specific item
    public function getPriceForItem($itemId)
    {
        $item = Item::find($itemId);
        if (!$item) return null;

        // Check if there's a custom price for this tier
        $customPrice = $this->itemPrices()
            ->where('item_id', $itemId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            })
            ->first();

        if ($customPrice) {
            if ($customPrice->custom_price) {
                return $customPrice->custom_price;
            }
            if ($customPrice->discount_percentage) {
                return $item->price * (1 - $customPrice->discount_percentage / 100);
            }
        }

        // Apply tier's global discount
        if ($this->discount_percentage > 0) {
            return $item->price * (1 - $this->discount_percentage / 100);
        }

        // Return original price
        return $item->price;
    }
}
