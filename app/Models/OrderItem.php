<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date: d M, Y',
    ];
    // public function item() {
    //     return $this->hasOne( Item::class,'id', 'item_id')->withTrashed();
    // }

    /**
     * علاقة بنوع الخدمة (غسيل / كي / غسيل وكوي)
     */
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function itemServiceType()
    {
        return $this->belongsTo(ItemServiceType::class);
    }

    /**
     * علاقة بالإضافات المرتبطة بهذا البند
     */
    public function addOns()
    {
        return $this->belongsToMany(
            AddOn::class,
            'order_item_add_on',
            'order_item_id',
            'add_on_id'
        )
            ->withPivot('price')
            ->withTimestamps();
    }

    /**
     * الصنف الأساسي المرتبط بهذا البند
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
