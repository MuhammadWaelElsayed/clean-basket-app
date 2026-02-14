<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Order;
use App\Models\Package;
use App\Models\PackageTransaction;
use Livewire\Component;
use League\Csv\Writer;

class PackageUsageReport extends Component
{
    public $report = [];

    public function mount()
    {
        abort_unless(auth()->user()->can('packages_reports'), 403);
        $this->loadData();
    }

    public function loadData()
    {
        $this->report = Package::with('userPackages')->get()->map(function ($package) {
            $userPackages = $package->userPackages;
            $subscriberCount = $userPackages->count();

            $userIds = $userPackages->pluck('user_id');
            $userPackageIds = $userPackages->pluck('id');

            $totalCredit = $userPackages->sum('total_credit');

            $totalConsumed = PackageTransaction::whereIn('user_package_id', $userPackageIds)
                ->where('type', 'debit')
                ->sum('amount');

            $ordersCount = Order::whereIn('user_id', $userIds)->count();

            $avgCreditUsed = $subscriberCount > 0 ? $totalConsumed / $subscriberCount : 0;
            $consumptionPercent = $totalCredit > 0 ? ($totalConsumed / $totalCredit) * 100 : 0;

            $autoRenewCount = $userPackages->where('auto_renew', 1)->count();
            $autoRenewPercent = $subscriberCount > 0 ? ($autoRenewCount / $subscriberCount) * 100 : 0;

            return [
                'name'                 => $package->name_en,
                'subscribers'          => $subscriberCount,
                'orders_count'         => $ordersCount,
                'avg_credit_used'      => round($avgCreditUsed, 2),
                'consumption_percent'  => round($consumptionPercent, 2),
                'auto_renew_percent'   => round($autoRenewPercent, 2),
            ];
        });
    }


    public function export()
    {
        $this->loadData(); // تحديث البيانات

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->insertOne(['Package', 'Subscribers', 'Orders', 'Avg. Credit Used', 'Consumption %', 'Auto Renew %']);

        foreach ($this->report as $row) {
            $csv->insertOne([
                $row['name'],
                $row['subscribers'],
                $row['orders_count'],
                number_format($row['avg_credit_used'], 2),
                number_format($row['consumption_percent'], 2) . '%',
                number_format($row['auto_renew_percent'], 2) . '%',
            ]);
        }

        $filename = 'package_usage_report_' . date('Y-m-d') . '.csv';
        $file = fopen('php://temp', 'w+');
        fwrite($file, $csv->getContent());
        rewind($file);

        return response()->streamDownload(function () use ($file) {
            fpassthru($file);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.packages.package-usage-report')
            ->layout('components.layouts.admin-dashboard');
    }
}
