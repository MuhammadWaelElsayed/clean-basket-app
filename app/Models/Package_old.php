<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;


class Package_old extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded=[];

    protected $casts = [
        'created_at' => 'date: d M, Y',
    ];
    protected function features(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }
    protected function featuresAr(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }
    protected $hidden = [

        'updated_at',
        'deleted_at',
    ];


    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => asset('storage/uploads/'.$value??'blank.png')
,
            set: fn ($value) => strtolower($value),
        );
    }

}
