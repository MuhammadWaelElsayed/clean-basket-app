<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];


    protected $casts = [
        'created_at' => 'date: d M, Y',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => asset('storage/uploads/' . ($value ?: 'blank.png')),
            set: fn($value) => strtolower($value),
        );
    }


    public function service()
    {
        return  $this->belongsTo(Service::class);
    }


    public function serviceTypes(): BelongsToMany
    {
        return $this->belongsToMany(
                ServiceType::class,
                'item_service_type'
            )
            ->withPivot(['price', 'discount_price', 'order_priority_id'])
            ->withTimestamps();
    }

    /**
     * علاقة بالتصنيف (categories/services)
     */
    public function category()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * علاقة many-to-many مع Service Categories
     */
    public function serviceCategories()
    {
        return $this->belongsToMany(
            Service::class,
            'item_service_categories',
            'item_id',
            'service_id'
        )->withTimestamps();
    }

    public function orderedServices(int $priorityId)
{
    return $this->serviceTypes()
                ->wherePivot('order_priority_id', $priorityId);
}


     /**
     * علاقة many-to-many إلى service_types
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
            ServiceType::class,       // موديل الخدمات
            'item_service_type',      // جدول الربط
            'item_id',                // هذا المفتاح في pivot يشير إلى items.id
            'service_type_id'         // هذا المفتاح في pivot يشير إلى service_types.id
        )
        ->withPivot('price', 'discount_price')
        ->withTimestamps();
    }

        /**
     * جميع الأسعار المرتبطة بهذا الصنف، لكل توليفة service_type + order_priority
     */
    public function itemServiceTypes()
    {
        return $this->hasMany(ItemServiceType::class);
    }

    /**
     * علاقة many-to-many إلى addons
     */
    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(
            AddOn::class,
            'order_item_add_on',
            'order_item_id',  // pivot إلى order_items
            'add_on_id'
        )
        ->withPivot('price')
        ->withTimestamps();
    }

    // Tier prices for this item
    public function tierPrices()
    {
        return $this->hasMany(B2bItemPrice::class);
    }

    // Client-specific prices for this item
    public function clientPrices()
    {
        return $this->hasMany(B2bClientItemPrice::class);
    }

    // Get price for a specific B2B client
    public function getPriceForClient($clientId)
    {
        $client = B2bClient::find($clientId);
        return $client ? $client->getPriceForItem($this->id) : $this->price;
    }

    // Get all tiers with their prices for this item
    public function getPricesByTier()
    {
        $tiers = B2bPricingTier::where('is_active', true)->get();

        return $tiers->map(function ($tier) {
            return [
                'tier_id' => $tier->id,
                'tier_name' => $tier->name,
                'tier_name_ar' => $tier->name_ar,
                'original_price' => $this->price,
                'tier_price' => $tier->getPriceForItem($this->id),
            ];
        });
    }
}
