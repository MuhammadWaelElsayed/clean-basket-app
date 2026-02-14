<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_package_id',
        'type',
        'amount',
        'description',
        'related_order_id'
    ];

    public function userPackage()
    {
        return $this->belongsTo(UserPackage::class);
    }
}
