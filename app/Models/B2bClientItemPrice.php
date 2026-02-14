<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2bClientItemPrice extends Model
{
    protected $fillable = [
        'b2b_client_id',
        'item_id',
        'custom_price',
        'discount_percentage',
        'effective_from',
        'effective_until',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'custom_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(B2bClient::class, 'b2b_client_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

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
