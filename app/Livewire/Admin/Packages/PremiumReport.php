<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Order;
use App\Models\UserPackage;
use League\Csv\Writer;
use Livewire\Component;

class PremiumReport extends Component
{
    public $summary = [];

    public function mount()
    {
        abort_unless(auth()->user()->can('packages_reports'), 403);
        $this->loadData();
    }

    public function loadData()
    {
        $premiumUsers = UserPackage::where('package_id', 3)->pluck('user_id');

        $orders = Order::whereIn('user_id', $premiumUsers)
            ->where('status', 'DELIVERED')
            ->get();

        $totalOrders = $orders->count();
        $uniqueUsers = $orders->pluck('user_id')->unique()->count();
        $avgOrdersPerUser = $uniqueUsers > 0 ? $totalOrders / $uniqueUsers : 0;

        $this->summary = [
            'total_orders'         => $totalOrders,
            'unique_users'         => $uniqueUsers,
            'avg_orders_per_user'  => round($avgOrdersPerUser, 2),
        ];
    }

    public function export()
    {
        $this->loadData();

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->insertOne(['Metric', 'Value']);

        foreach ($this->summary as $key => $value) {
            $csv->insertOne([ucwords(str_replace('_', ' ', $key)), $value]);
        }

        $filename = 'premium_package_report_' . date('Y-m-d') . '.csv';
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
        return view('livewire.admin.packages.premium-report')
            ->layout('components.layouts.admin-dashboard');
    }
}
