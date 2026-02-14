<?php

namespace App\Livewire\Admin\Vouchers;

use App\Models\Voucher;
use Livewire\Component;

class ManageVouchers extends Component
{
    public $vouchers;
    public $dId;

    public function mount()
    {
        $this->loadVouchers();
    }

    public function loadVouchers()
    {
        $this->vouchers = Voucher::orderBy('id', 'desc')->get();
    }

    public function setDel($id)
    {
        $this->dId = $id;
    }

    public function delVoucher()
    {
        \App\Models\Voucher::findOrFail($this->dId)->delete();
        $this->dispatch('success', 'Voucher Deleted Successfully!');
        $this->loadVouchers();
    }



    public function render()
    {
        return view('livewire.admin.vouchers.manage-vouchers')
            ->layout('components.layouts.admin-dashboard');
    }
}
