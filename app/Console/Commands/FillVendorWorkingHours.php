<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillVendorWorkingHours extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vendors:fill-working-hours
                            {--vendor-id= : Fill hours for specific vendor only}
                            {--force : Overwrite existing hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill working hours for all vendors. Vendors with only 1 day get set to 24/7 for all days.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Only allow on local environment
        if (!app()->environment('local')) {
            $this->error('This command can only be run in local environment!');
            $this->warn('Current environment: ' . app()->environment());
            return 1;
        }

        $vendorId = $this->option('vendor-id');
        $force = $this->option('force');

        // Get vendors to process
        $vendorsQuery = DB::table('vendors')->select('id', 'business_name');

        if ($vendorId) {
            $vendorsQuery->where('id', $vendorId);
        }

        $vendors = $vendorsQuery->get();

        if ($vendors->isEmpty()) {
            $this->error('No vendors found.');
            return 1;
        }

        $this->info("Processing {$vendors->count()} vendor(s)...");
        $this->newLine();

        $processed = 0;
        $skipped = 0;

        foreach ($vendors as $vendor) {
            $result = $this->processVendor($vendor->id, $vendor->business_name ?? "Vendor #{$vendor->id}", $force);

            if ($result) {
                $processed++;
            } else {
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("✓ Processed: {$processed}");
        if ($skipped > 0) {
            $this->warn("⊘ Skipped (already has hours): {$skipped}");
        }

        return 0;
    }

    /**
     * Process a single vendor
     */
    protected function processVendor(int $vendorId, string $vendorName, bool $force): bool
    {
        // Check existing working hours
        $existingCount = DB::table('vendor_working_hours')
            ->where('vendor_id', $vendorId)
            ->count();

        // If vendor already has hours and not forcing, skip
        if ($existingCount > 0 && !$force) {
            $this->line("Skipping {$vendorName} (already has {$existingCount} working hour records)");
            return false;
        }

        // If forcing, delete existing records
        if ($force && $existingCount > 0) {
            DB::table('vendor_working_hours')
                ->where('vendor_id', $vendorId)
                ->delete();
            $this->line("Deleted {$existingCount} existing records for {$vendorName}");
        }

        // Check if vendor has exactly 1 day configured
        $uniqueDays = DB::table('vendor_working_hours')
            ->where('vendor_id', $vendorId)
            ->distinct()
            ->pluck('day_of_week')
            ->count();

        // Determine if we should set 24/7 for all days
        $shouldFillAllDays = $uniqueDays <= 1;

        if ($shouldFillAllDays) {
            // Fill all 7 days with 24-hour schedule
            $records = [];
            for ($day = 0; $day <= 6; $day++) {
                $records[] = [
                    'vendor_id' => $vendorId,
                    'day_of_week' => $day,
                    'open_time' => '00:00:00',
                    'close_time' => '23:59:59',
                    'is_closed' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('vendor_working_hours')->insert($records);
            $this->info("✓ {$vendorName}: Set 24/7 for all days (Sunday-Saturday)");
        } else {
            $this->line("✓ {$vendorName}: Already has multiple days configured, keeping existing schedule");
        }

        return true;
    }
}
