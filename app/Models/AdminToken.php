<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class AdminToken extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded=[];
    
    protected $table='admin_tokens';


    protected $casts = [
        'created_at' => 'date: d M, Y',
    ];

    protected $hidden = [
        'updated_at',
    ];
    


}
