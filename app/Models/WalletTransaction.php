<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'wallet_id',
        'type',
        'amount',
        'vat_amount',
        'source',
        'description',
        'related_order_id',
        'payment_response'
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'related_order_id');
    }

    public function userPackage()
    {
        return $this->belongsTo(UserPackage::class, 'user_package_id');
    }


    protected $casts = [
        'payment_response' => 'array',
    ];
}
