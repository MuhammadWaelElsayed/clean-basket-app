<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class B2BPartnerSecret extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'b2b_partner_secrets';

}
