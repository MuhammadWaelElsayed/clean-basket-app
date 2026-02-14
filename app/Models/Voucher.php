<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'type',
        'amount',
        'max_usage',
        'expiry_date',
        'note',
    ];

    // كل القسائم الممنوحة لهذا النوع
    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }
}
