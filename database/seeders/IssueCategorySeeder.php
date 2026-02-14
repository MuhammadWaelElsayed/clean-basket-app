<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IssueCategory;

class IssueCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Current Order' => 'الطلب الحالي',
            'Past Order' => 'طلب سابق',
            'Other Inquiries' => 'استفسارات أخرى',
            'Technical Issues' => 'مشكلات تقنية',
            'Suggestions' => 'اقتراحات'
        ];

        foreach ($categories as $name => $name_ar) {
            IssueCategory::create([
                'name' => $name,
                'name_ar' => $name_ar,
            ]);
        }
    }
}
