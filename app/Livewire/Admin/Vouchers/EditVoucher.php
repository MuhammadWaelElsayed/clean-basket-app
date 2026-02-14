<?php

namespace App\Livewire\Admin\Vouchers;

use App\Models\Voucher;
use Livewire\Component;

class EditVoucher extends Component
{
    public $voucher;
    public $type;
    public $amount;
    public $max_usage;
    public $expiry_date;
    public $note;

    public function mount(Voucher $voucher)
    {
        $this->voucher     = $voucher;
        $this->type        = $voucher->type;
        $this->amount      = $voucher->amount;
        $this->max_usage   = $voucher->max_usage;
        $this->expiry_date = $voucher->expiry_date ? date('Y-m-d', strtotime($voucher->expiry_date)) : null;
        $this->note        = $voucher->note;
    }

    public function update()
    {
        $this->validate([
            'type'        => 'required|string|max:50',
            'amount'      => 'nullable|numeric|min:0',
            'max_usage'   => 'required|integer|min:1',
            'expiry_date' => 'nullable|date',
            'note'        => 'nullable|string|max:255',
        ]);

        $this->voucher->update([
            'type'        => $this->type,
            'amount'      => $this->amount,
            'max_usage'   => $this->max_usage,
            'expiry_date' => $this->expiry_date,
            'note'        => $this->note,
        ]);

        session()->flash('success', 'Voucher updated successfully.');
        return redirect()->route('admin.vouchers');
    }

    public function render()
    {
        return view('livewire.admin.vouchers.edit-voucher')
            ->layout('components.layouts.admin-dashboard');
    }
}
