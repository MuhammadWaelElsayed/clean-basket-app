<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\B2bPricingTier;

class B2bPricingTierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Bronze Tier',
                'name_ar' => 'مستوى برونزي',
                'description' => 'Basic pricing for new clients',
                'description_ar' => 'تسعير أساسي للعملاء الجدد',
                'discount_percentage' => 5.00,
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Silver Tier',
                'name_ar' => 'مستوى فضي',
                'description' => 'Better pricing for regular clients',
                'description_ar' => 'تسعير أفضل للعملاء المنتظمين',
                'discount_percentage' => 10.00,
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Gold Tier',
                'name_ar' => 'مستوى ذهبي',
                'description' => 'Premium pricing for VIP clients',
                'description_ar' => 'تسعير مميز لعملاء VIP',
                'discount_percentage' => 15.00,
                'priority' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Platinum Tier',
                'name_ar' => 'مستوى بلاتيني',
                'description' => 'Best pricing for enterprise clients',
                'description_ar' => 'أفضل تسعير لعملاء المؤسسات',
                'discount_percentage' => 20.00,
                'priority' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($tiers as $tier) {
            B2bPricingTier::create($tier);
        }
    }
}
