<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalDriver extends Model
{
    protected $fillable = [
        'external_driver_id',
        'name',
        'phone',
        'email',
        'provider',
        'profile_image',
    ];

    /**
     * Scope to filter by provider
     */
    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope to filter by Leajlak provider
     */
    public function scopeLeajlak($query)
    {
        return $query->where('provider', 'leajlak');
    }
}
