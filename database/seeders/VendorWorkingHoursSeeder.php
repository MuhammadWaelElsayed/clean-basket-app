<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VendorWorkingHours;
use App\Models\Vendor;

class VendorWorkingHoursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = Vendor::where('status', 1)->where('is_approved', 1)->get();

        $daysOfWeek = VendorWorkingHours::getDaysOfWeek();

        foreach ($vendors as $vendor) {
            foreach ($daysOfWeek as $day) {
                // Skip Friday (day 5) for some vendors to simulate closed days
                if ($day['value'] == 5 && $vendor->id % 3 == 0) {
                    VendorWorkingHours::create([
                        'vendor_id' => $vendor->id,
                        'day_of_week' => $day['value'],
                        'day_en' => $day['en'],
                        'day_ar' => $day['ar'],
                        'open_time' => null,
                        'close_time' => null,
                        'is_closed' => true,
                    ]);
                    continue;
                }

                // Regular working hours (8 AM to 8 PM)
                VendorWorkingHours::create([
                    'vendor_id' => $vendor->id,
                    'day_of_week' => $day['value'],
                    'day_en' => $day['en'],
                    'day_ar' => $day['ar'],
                    'open_time' => '08:00:00',
                    'close_time' => '20:00:00',
                    'is_closed' => false,
                ]);
            }
        }
    }
}
