<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class OrderTracking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded=[];
    protected $table='order_tracking';

    public function getCreatedAtAttribute($date)
    {
        // dd($this->order->country);
        $timezone=env('APP_TIMEZONE');
        return Carbon::parse($date)->setTimezone($timezone)->format('d M, h:i A');
    }

    public function order() {
        return $this->belongsTo(Order::class);
    }
}
