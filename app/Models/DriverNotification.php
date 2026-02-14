<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class DriverNotification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $guarded=[];


    public function getCreatedAtAttribute($date)
    {
        $country=(auth('sanctum')->user())?auth('sanctum')->user()->country:'Lebanon';
        $timezone=($country=='Lebanon')?env('APP_TIMEZONE_LB'):env('APP_TIMEZONE_LB');
        return Carbon::parse($date)->setTimezone($timezone)->format('Y-m-d H:i');
    }


    public function driver() {
        return $this->belongsTo( Driver::class, 'driver_id');
    }
   
    
}
