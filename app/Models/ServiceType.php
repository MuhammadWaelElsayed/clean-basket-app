<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ServiceType extends Model
{
    protected $table = 'service_types';

    protected $fillable = [
        'name',
        'name_ar',
    ];

    /**
     * نوع الخدمة مرتبط بعدة أصناف (pivot)
     */
    public function items()
    {
        return $this->belongsToMany(
            Item::class,
            'item_service_type',
            'service_type_id',
            'item_id'
        )
        ->withPivot('price', 'discount_price')
        ->withTimestamps();
    }

    /**
     * نوع الخدمة مرتبط بعدة بنود بالطلب
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'service_type_id');
    }
}

