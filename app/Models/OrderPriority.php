<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPriority extends Model
{
    protected $table = 'order_priorities';

    protected $fillable = [
        'name',
        'name_ar',
        'lead_time',
     ];

    /**
     * أولوية الطلب تصل إلى عدة طلبات
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'order_priority_id');
    }
}
