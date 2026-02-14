<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $packages = [
            [
                'name' => 'الباقة الأساسية',
                'name_en' => 'Basic',
                'price' => 150,
                'cashback_amount' => 50,
                'delivery_fee' => 3,
                'duration_days' => 0,
                'has_priority' => false
            ],
            [
                'name' => 'الباقة المثالية',
                'name_en' => 'Standard',
                'price' => 300,
                'cashback_amount' => 150,
                'delivery_fee' => 0,
                'duration_days' => 0,
                'has_priority' => false
            ],
            [
                'name' => 'الباقة الثالثة',
                'name_en' => 'Premium',
                'price' => 500,
                'cashback_amount' => 200,
                'delivery_fee' => 0,
                'duration_days' => 0,
                'has_priority' => true
            ]
        ];

        foreach ($packages as $package) {
            Package::create($package);
        }
    }
}
