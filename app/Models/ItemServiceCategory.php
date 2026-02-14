<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'service_id',
    ];

    /**
     * علاقة بالعنصر
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * علاقة بالخدمة
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
