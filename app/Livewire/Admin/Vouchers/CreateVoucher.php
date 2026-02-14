<?php

namespace App\Livewire\Admin\Vouchers;

use App\Models\Voucher;
use Livewire\Component;

class CreateVoucher extends Component
{

    public $type = 'package';
    public $amount;
    public $max_usage = 1;
    public $expiry_date;
    public $note;

    protected $rules = [
        'type'       => 'required|string|max:50',
        'amount'     => 'nullable|numeric|min:0',
        'max_usage'  => 'required|integer|min:1',
        'expiry_date'=> 'nullable|date',
        'note'       => 'nullable|string|max:255',
    ];

    public function create()
    {
        $this->validate();

        Voucher::create([
            'type'        => $this->type,
            'amount'      => $this->amount,
            'max_usage'   => $this->max_usage,
            'expiry_date' => $this->expiry_date,
            'note'        => $this->note,
        ]);

        session()->flash('success', 'Voucher created successfully.');
        return redirect()->route('admin.vouchers');
    }

    public function render()
    {
        return view('livewire.admin.vouchers.create-voucher')
            ->layout('components.layouts.admin-dashboard');
    }
}
