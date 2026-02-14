<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;


class Onboard extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table='onboard_data';

    protected $guarded=[];

    protected $casts = [
        'created_at' => 'date: d M, Y',
    ];
    protected $hidden = [
        'updated_at',
    ];
    protected function media(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => asset('storage/uploads/'.$value??'blank.png'),
            set: fn ($value) => strtolower($value),
        );
    }
   
    
}
