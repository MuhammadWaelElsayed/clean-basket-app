<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'total_credit',
        'vat_amount',
        'remaining_credit',
        'start_date',
        'end_date',
        'is_active',
        'auto_renew',
        'payment_response'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function transactions()
    {
        return $this->hasMany(PackageTransaction::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'user_package_id');
    }


    protected $casts = [
        'payment_response' => 'array',
    ];
}
