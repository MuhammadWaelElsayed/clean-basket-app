<?php

namespace App\Livewire\Admin\Vouchers;

use App\Models\UserVoucher;
use App\Models\Voucher;
use Livewire\Component;

class ViewVoucher extends Component
{
    public $voucher;
    public $userVouchers;

    public function mount($voucher)
    {
        $this->voucher = Voucher::findOrFail($voucher);
        $this->userVouchers = UserVoucher::with('user')
            ->where('voucher_id', $this->voucher->id)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.vouchers.view-voucher')
            ->layout('components.layouts.admin-dashboard');
    }
}