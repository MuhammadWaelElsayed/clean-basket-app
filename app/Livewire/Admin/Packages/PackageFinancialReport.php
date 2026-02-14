<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Package;
use App\Models\PackageTransaction;
use App\Models\WalletTransaction;
use Livewire\Component;
use League\Csv\Writer;

class PackageFinancialReport extends Component
{
    public $report = [];

    public function mount()
    {
        abort_unless(auth()->user()->can('packages_finance'), 403);
        $this->loadData();
    }

    public function loadData()
    {
        $this->report = Package::with('userPackages')->get()->map(function ($package) {
            $userPackages = $package->userPackages;

            $subscribers = $userPackages->count();

            // ✅ مجموع السعر الفعلي المدفوع: إذا الرصيد ≥ السعر، اعتبر السعر الأصلي، وإلا خذ الرصيد كما هو
            $totalPrice = $userPackages->sum(function ($userPackage) use ($package) {
                return $userPackage->total_credit >= $package->price
                    ? $package->price
                    : ($userPackage->total_credit ?? 0);
            });

            // ✅ مجموع الرصيد الكلي (price + cashback)
            $totalCredit = $userPackages->sum('total_credit');

            // ✅ الكاش باك = الفرق بين الرصيد والسعر المدفوع
            $totalCashback = $totalCredit - $totalPrice;

            // ✅ استخرج IDs للـ user_packages
            $userPackageIds = $userPackages->pluck('id');

            // ✅ مجموع الرصيد المستهلك (من نوع debit فقط)
            $consumed = PackageTransaction::whereIn('user_package_id', $userPackageIds)
                ->where('type', 'debit')
                ->sum('amount');

            return [
                'name'         => $package->name_en,
                'subscribers'  => $subscribers,
                'price'        => $totalPrice,
                'cashback'     => $totalCashback,
                'total_credit' => $totalCredit,
                'consumed'     => $consumed,
            ];
        });
    }



    public function export()
    {

        // إعادة تحميل البيانات للتأكد من أن التقرير محدث
        $this->loadData();

        // إنشاء ملف CSV مؤقت
        $csv = Writer::createFromFileObject(new \SplTempFileObject());

        // رأس التقرير
        $csv->insertOne(['Package', 'Subscribers', 'Total Revenue',   'Consumed Credit']);

        // إدخال الصفوف من التقرير
        foreach ($this->report as $row) {
            $csv->insertOne([
                $row['name'],
                $row['subscribers'],
                number_format($row['revenue'], 2),
                 number_format($row['consumed'], 2),
            ]);
        }

        $filename = 'package_financial_report_' . date('Y-m-d') . '.csv';
        $file = fopen('php://temp', 'w+');
        fwrite($file, $csv->getContent());
        rewind($file);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->streamDownload(function () use ($file) {
            fpassthru($file);
        }, $filename, $headers);
    }

    public function render()
    {
        return view('livewire.admin.packages.package-financial-report')
            ->layout('components.layouts.admin-dashboard');
    }

}
