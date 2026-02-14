<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'user_id', 'response', 'amount', 'vat_amount',
        'payment_method', 'transaction_id', 'payment_reference',
        'status', 'refund_reference', 'refund_response', 'refund_notes'
    ];

    protected $casts = [
        'response' => 'array',
        'refund_response' => 'array',
        'created_at' => 'date: d M, Y',
    ];

    protected $hidden = [
        'updated_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

