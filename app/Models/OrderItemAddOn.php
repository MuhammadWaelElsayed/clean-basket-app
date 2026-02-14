<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class OrderItemAddOn extends Pivot
{
    protected $table = 'order_item_add_on';

    protected $fillable = [
        'order_item_id',
        'add_on_id',
        'price',
    ];
}
