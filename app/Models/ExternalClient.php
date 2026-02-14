<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalClient extends Model
{
    protected $fillable = [
        'name','external_number','client_id','client_secret_hash','is_active','token_ttl'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'token_ttl' => 'integer',
    ];
}
