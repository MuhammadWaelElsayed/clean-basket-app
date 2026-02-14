<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerInquery extends Model
{
    use HasFactory;

    protected $guarded=[];

    
    protected $casts = [
        'created_at' => 'date: d M, Y',
    ];
    protected $hidden = [
        'updated_at',
    ];
    
}

