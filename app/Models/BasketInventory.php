<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class BasketInventory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded=[];

    protected $table='basket_inventory';


    protected $casts = [
        'created_at' => 'date: d M, Y',
    ];
    protected $hidden = [
        'updated_at',
    ];
    
   
}
