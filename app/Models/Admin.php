<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class Admin extends Authenticatable
{
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected string $guard_name = 'admin';
    protected $guarded = [];
    protected $table = 'admin_users';

    protected $casts = [
        'created_at' => 'date: d M, Y',
    ];

    protected $hidden = [
        'password',
        'email_verified_at',
        'email_verify_token',
        'remember_token',
        'deviceToken',
    ];

    // Auto-hash passwords
    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Hash::needsRehash($value) ? Hash::make($value) : $value,
        );
    }

    protected function picture(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => asset('/uploads/blank.png'),
            set: fn ($value) => strtolower($value),
        );
    }
}
