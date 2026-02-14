<?php

namespace App\Livewire\Admin\Wallet;

use Livewire\Component;
use App\Models\WalletSetting;

class Settings extends Component
{
    public $min_balance;
    public $max_balance;
    public $balance_validity_days;
    public $settingId;

    public function mount()
    {
        abort_unless(auth()->user()->can('wallet_settings'), 403);
        $setting = WalletSetting::first();

        if ($setting) {
            $this->settingId = $setting->id;
            $this->min_balance = $setting->min_balance;
            $this->max_balance = $setting->max_balance;
            $this->balance_validity_days = $setting->balance_validity_days;
        }
    }

    public function save()
    {
        $this->validate([
            'min_balance' => 'required|numeric|min:0',
            'max_balance' => 'nullable|numeric|min:0',
            'balance_validity_days' => 'nullable|integer|min:0',
        ]);

        WalletSetting::updateOrCreate(
            ['id' => $this->settingId],
            [
                'min_balance' => $this->min_balance,
                'max_balance' => $this->max_balance,
                'balance_validity_days' => $this->balance_validity_days,
            ]
        );
        $this->reset(['min_balance', 'max_balance', 'balance_validity_days']);
        session()->flash('success', 'Wallet settings updated successfully!');
    }

    public function render()
    {
        return view('livewire.admin.wallet.settings')
            ->layout('components.layouts.admin-dashboard');
    }
}
