<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class UserPromoCode extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded=[];
    protected $table='user_promo_codes';


    protected $casts = [
        'created_at' => 'date: d M, Y',
    ];
    protected $hidden = [
        'updated_at',
    ];
    public function promoCode() {
        return $this->hasOne( PromoCode::class,'id', 'code_id');
    }
    public function user() {
        return $this->hasOne( User::class,'id', 'user_id');
    }
   
}
