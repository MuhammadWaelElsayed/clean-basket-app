<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Models\VendorPackage;
use Carbon\Carbon;

class Vendor extends Model
{
    use  HasApiTokens,HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded=[];
    // protected $appends = ['subscription_status'];


    protected $casts = [
        'created_at' => 'date: d M, Y',
        // 'areas' => 'json',
    ];

    protected $hidden = [
        'otp',
        'password',
        // 'deviceToken',
        'updated_at',
        'deleted_at',
        'stripe_customer_id',
        'stripe_customer_id',
    ];

    protected function picture(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => asset('storage/uploads').'/'.($value??'blank.png'),
            set: fn ($value) => $value,
        );
    }

    protected function areas(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value,true),
            set: fn ($value) => json_encode($value),
        );
    }

    public function city() {
        return  $this->belongsTo(City::class);
    }

    public function area() {
        return  $this->belongsTo(Area::class);
    }

    public function workingHours() {
        return $this->hasMany(VendorWorkingHours::class);
    }


     //Append a New Atribute
    // public function getSubscriptionStatusAttribute() {
    //     $now=Carbon::now();

    //     $package = VendorPackage::with('package')->where(['vendor_id'=>$this->id,'is_addon'=>0])->latest('id')->first();
    //     if ($package!=null) {
    //         if($package->is_cancelled==1){
    //             $package_status="Cancelled";
    //         }
    //         else if($package->expired_at < $now){
    //             $package_status="Expired";
    //         }else{
    //             $package_status="Active";
    //         }

    //     }else{
    //         $package_status="None";
    //     }
    //     return $package_status;
    // }


}
