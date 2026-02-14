<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use App\Models\Area;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded=[];
    protected $table='user_address';

    protected $appends = ['area_id'];
    
    protected $casts = [
        'created_at' => 'date: d M, Y',
    ];
    protected $hidden = [
        'updated_at',
        'vendor_id',
        'driver_id',
        'deleted_at',
    ];

    public function getAreaAttribute($val)
    {
        $area=Area::where('id',$val)->first();
        return $area->name?? '--';
    }
    public function getAreaIdAttribute()
    {
        return $this->attributes['area'];
    }
    public function user() {
        return  $this->belongsTo(User::class)->withTrashed();
    }
    public function client() {
        return  $this->belongsTo(B2bClient::class, 'client_id');
    }
    public function vendor() {
        return  $this->belongsTo(Vendor::class)->withTrashed();
    }
    public function driver() {
        return  $this->belongsTo(Driver::class)->withTrashed();
    }


}
