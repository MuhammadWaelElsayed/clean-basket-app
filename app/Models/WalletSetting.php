<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_balance',
        'max_balance',
        'balance_validity_days',
    ];

    private function getWalletSettings()
    {
        return WalletSetting::first();
    }
}
