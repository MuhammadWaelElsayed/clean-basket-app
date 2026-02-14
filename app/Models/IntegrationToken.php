<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationToken extends Model
{
    protected $fillable = [
        'name',
        'provider',
        'token_hash',
        'token_hint',
        'encrypted_token',
        'scopes',
        'is_active',
        'expires_at',
        'rotated_at',
        'revoked_at',
        'last_used_at',
        'use_count',
    ];

    protected $casts = [
        'scopes'      => 'array',
        'is_active'   => 'boolean',
        'expires_at'  => 'datetime',
        'rotated_at'  => 'datetime',
        'revoked_at'  => 'datetime',
        'last_used_at'=> 'datetime',
    ];
}
