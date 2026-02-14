<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingsServiceFee extends Model
{
    use HasFactory;

    protected $table = 'settings_service_fee';

    protected $fillable = [
        'is_enabled',
        'minimum_order_amount',
        'service_fee_amount',
        'description',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'minimum_order_amount' => 'decimal:2',
        'service_fee_amount' => 'decimal:2',
    ];

    /**
     * الحصول على الإعدادات النشطة
     */
    public static function getActiveSettings()
    {
        return self::first();
    }

    /**
     * تحديث الإعدادات
     */
    public static function updateSettings($data)
    {
        $settings = self::first();

        if (!$settings) {
            $settings = self::create($data);
        } else {
            $settings->update($data);
        }

        return $settings;
    }
}
