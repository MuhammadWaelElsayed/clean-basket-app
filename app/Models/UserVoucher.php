<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVoucher extends Model
{
    protected $fillable = [
        'user_id',
        'voucher_id',
        'code',
        'remaining_uses',
        'remaining_amount',
        'assigned_at',
        'expired_at',
        'is_active',
        'gifted_to_user_id',
        'gifted_to_phone',
        'gifted_at',
    ];

    // protected static function booted()
    // {
    //     static::creating(function ($uv) {
    //         // if remaining_amount is not passed manually, start it with voucher.amount
    //         if (is_null($uv->remaining_amount) && $uv->voucher) {
    //             $uv->remaining_amount = $uv->voucher->amount;
    //         }
    //     });
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
    // app/Models/UserVoucher.php

    public function orders()
    {
        return $this->hasMany(Order::class, 'voucher_id');
    }

}
