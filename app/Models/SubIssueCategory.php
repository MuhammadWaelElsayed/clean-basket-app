<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubIssueCategory extends Model
{
    protected $table = 'sub_issue_categories';
    protected $fillable = ['name', 'name_ar', 'issue_category_id'];

    public function mainCategory()
    {
        return $this->belongsTo(IssueCategory::class, 'issue_category_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'sub_issue_category_id');
    }
}
