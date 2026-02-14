<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class OrdersReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:orders-report {start_date?} {end_date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export orders data to Excel for a given date range';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startDate = $this->argument('start_date')
            ? Carbon::parse($this->argument('start_date'))->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();

        $endDate = $this->argument('end_date')
            ? Carbon::parse($this->argument('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        $filename = 'orders_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.xlsx';

        $this->info("Exporting orders from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        try {
            Excel::store(new OrdersExport($startDate, $endDate), $filename, 'public');

            $filePath = storage_path('app/public/' . $filename);
            $this->info("Orders report exported successfully: {$filePath}");

        } catch (\Exception $e) {
            $this->error("Error exporting orders: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
