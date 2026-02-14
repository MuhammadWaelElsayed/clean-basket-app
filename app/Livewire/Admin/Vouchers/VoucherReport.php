<?php

namespace App\Livewire\Admin\Vouchers;

use App\Models\Package;
use App\Models\UserVoucher;
use Livewire\Component;

class VoucherReport extends Component
{
    public $report = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->report = Package::all()->map(function ($package) {
            $granted = UserVoucher::whereHas('voucher', function ($q) {
                $q->where('type', 'package');
            })->where('voucher_id', $package->id)->count();

            $used = UserVoucher::whereHas('voucher', function ($q) {
                $q->where('type', 'package');
            })->where('voucher_id', $package->id)
              ->where('remaining_uses', 0)
              ->count();

            return [
                'name'      => $package->name_en,
                'granted'   => $granted,
                'used'      => $used,
                'remaining' => $granted - $used,
                'usage_rate'=> $granted > 0 ? round($used / $granted * 100, 2) : 0,
            ];
        });
    }


    public function render()
    {
        return view('livewire.admin.vouchers.voucher-report')
            ->layout('components.layouts.admin-dashboard');
    }

}
