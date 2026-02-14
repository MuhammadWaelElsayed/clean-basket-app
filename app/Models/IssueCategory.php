<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueCategory extends Model
{
    protected $fillable = ['name', 'name_ar'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function subCategories()
    {
        return $this->hasMany(SubIssueCategory::class, 'issue_category_id');
    }
}
