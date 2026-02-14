<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;


class Area extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded=[];

    protected $casts = [
        'created_at' => 'date: d M, Y',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    public function city() {
        return  $this->belongsTo(City::class)->withTrashed();
    }
}
