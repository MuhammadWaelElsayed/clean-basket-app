<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;


class Driver extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded=[];

    protected $casts = [
        'created_at' => 'date: d M, Y',
        'last_online' => 'date: d M, h:i A',
        'is_online' => 'boolean',
        'is_free' => 'boolean',
        'is_available' => 'boolean',
        'device_info' => 'array',
    ];

    protected $hidden = [
        'updated_at',
        'password',
        'deleted_at',
    ];
    protected function picture(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => asset('storage/uploads').'/'.($value??'blank.png'),
            set: fn ($value) => strtolower($value),
        );
    }
    protected function license(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => asset('storage/uploads').'/'.($value),
            set: fn ($value) => strtolower($value),
        );
    }
    protected function idImage(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => asset('storage/uploads').'/'.($value),
            set: fn ($value) => strtolower($value),
        );
    }
    public function vendor() {
        return  $this->belongsTo(Vendor::class)->withTrashed();
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'driver_vendor')->withTrashed();
    }

  //Append a New Atribute
    // public function getIsFreeAttribute() {
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
