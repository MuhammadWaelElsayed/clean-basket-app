<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorWorkingHours extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'day_of_week',
        'day_en',
        'day_ar',
        'open_time',
        'close_time',
        'is_closed'
    ];

    protected $casts = [
        'is_closed' => 'boolean',
        'open_time' => 'datetime:H:i',
        'close_time' => 'datetime:H:i',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public static function getDaysOfWeek()
    {
        return [
            ['value' => 0, 'en' => 'Sunday', 'ar' => 'الأحد'],
            ['value' => 1, 'en' => 'Monday', 'ar' => 'الاثنين'],
            ['value' => 2, 'en' => 'Tuesday', 'ar' => 'الثلاثاء'],
            ['value' => 3, 'en' => 'Wednesday', 'ar' => 'الأربعاء'],
            ['value' => 4, 'en' => 'Thursday', 'ar' => 'الخميس'],
            ['value' => 5, 'en' => 'Friday', 'ar' => 'الجمعة'],
            ['value' => 6, 'en' => 'Saturday', 'ar' => 'السبت'],
        ];
    }
}
