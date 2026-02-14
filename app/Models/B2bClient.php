<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class B2bClient extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'company_name',
        'contact_person',
        'email',
        'password',
        'phone',
        'tax_number',
        'address',
        'service_fees',
        'delivery_fees',
        'credit_limit',
        'pricing_tier_id',
        'is_active',
        'vendor_id',
        'driver_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Pricing tier relationship
    public function pricingTier()
    {
        return $this->belongsTo(B2bPricingTier::class);
    }

    public function vendor() {
        return  $this->belongsTo(Vendor::class)->withTrashed();
    }
    public function driver() {
        return  $this->belongsTo(Driver::class)->withTrashed();
    }
    // Client-specific custom prices
    public function customItemPrices()
    {
        return $this->hasMany(B2bClientItemPrice::class);
    }

    // Get price for an item (with priority: client-specific > tier > original)
    public function getPriceForItem($itemId)
    {
        $item = Item::find($itemId);
        if (!$item) return null;

        // 1. Check client-specific price (highest priority)
        $clientPrice = $this->customItemPrices()
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

        if ($clientPrice) {
            return $clientPrice->getFinalPrice();
        }

        // 2. Check tier price
        if ($this->pricingTier) {
            return $this->pricingTier->getPriceForItem($itemId);
        }

        // 3. Return original price
        return $item->price;
    }

    // Get all items with client's prices
    public function getItemsWithPrices()
    {
        $items = Item::where('status', true)->get();

        return $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'name_ar' => $item->name_ar,
                'original_price' => $item->price,
                'client_price' => $this->getPriceForItem($item->id),
                'discount' => $item->price - $this->getPriceForItem($item->id),
                'discount_percentage' => $item->price > 0
                    ? round((($item->price - $this->getPriceForItem($item->id)) / $item->price) * 100, 2)
                    : 0,
            ];
        });
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'client_id')->latest();
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id')->where('type', 'b2b')->latest();
    }
}
