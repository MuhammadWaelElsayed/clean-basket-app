<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IssueCategory;
use App\Models\SubIssueCategory;

class SubIssueCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subCategories = [
            'Current Order' => [
                'Delivery Time' => 'وقت التسليم',
                'Modify Order' => 'تعديل الطلب',
                'Cancel Order' => 'إلغاء الطلب',
                'Change Location' => 'تغيير الموقع',
                 ],
            'Past Order' => [
                'Wrong Order' => 'طلب خاطئ',
                'Service Feedback' => 'تقييم الخدمة',
                'Missing Items' => 'العناصر المفقودة',
            ],
            'Other Inquiries' => [
                'Prices' => 'الأسعار',
                'Service Hours' => 'ساعات الخدمة',
                'Service Areas' => 'مناطق الخدمة',
                'Service Duration' => 'مدة الخدمة',
                'Service Types' => 'أنواع الخدمة',
            ],
            'Technical Issues' => [
                'Payment Issue' => 'مشكلة في الدفع',
                'App Issue' => 'مشكلة في التطبيق',
            ],
            'Suggestions' => [
                'Suggestions' => 'اقتراحات',
            ],
        ];

        foreach ($subCategories as $mainCategory => $subs) {
            $category = IssueCategory::where('name', $mainCategory)->orWhere('name_ar', $mainCategory)->first();
            if ($category) {
                foreach ($subs as $subName => $subNameAr) {
                    SubIssueCategory::create([
                        'name' => $subName,
                        'name_ar' => $subNameAr,
                        'issue_category_id' => $category->id,
                    ]);
                }
            }
        }
    }
}
