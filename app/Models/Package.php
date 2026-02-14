<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'price',
        'vat',
        'total_price',
        'cashback_amount',
        'delivery_fee',
        'duration_days',
        'has_priority'
    ];

    public function userPackages()
    {
        return $this->hasMany(UserPackage::class);
    }
}
