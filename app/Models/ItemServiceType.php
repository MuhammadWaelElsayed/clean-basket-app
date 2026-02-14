<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemServiceType extends Model
{
    use HasFactory;

    // اسم الجدول صراحة لأنه لا يتبع الصيغة الجمعية الافتراضية
    protected $table = 'item_service_type';

    // الحقول القابلة للتعبئة
    protected $fillable = [
        'item_id',
        'service_type_id',
        'order_priority_id',
        'price',
        'discount_price',
    ];

    /**
     * علاقة إلى الصنف (Item)
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * علاقة إلى نوع الخدمة (ServiceType)
     */
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    /**
     * علاقة إلى الأولوية (OrderPriority)
     */
    public function orderPriority()
    {
        return $this->belongsTo(OrderPriority::class, 'order_priority_id');
    }
}
