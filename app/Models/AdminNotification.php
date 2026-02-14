<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use App\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

// #[ObservedBy([NotificationObserver::class])]
class AdminNotification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded=[];

    // protected $casts = [
    //     'created_at' => 'date: d M, Y',
    // ];
    
    protected $hidden = [
        'updated_at',
    ];
}
