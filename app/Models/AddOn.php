<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddOn extends Model
{
    protected $table = 'add_ons';

    protected $fillable = [
        'name',
        'name_ar',
        'price',
    ];

    /**
     * الإضافات مرتبطة بعنصر طلب واحد–لكل–عديد
     */
    public function orderItems()
    {
        return $this->belongsToMany(
            OrderItem::class,
            'order_item_add_on',
            'add_on_id',
            'order_item_id'
        )
            ->withPivot('price')
            ->withTimestamps();
    }
}
