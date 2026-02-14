<?php

namespace App\Livewire\Admin\ServiceFee;

use Livewire\Component;
use App\Models\SettingsServiceFee;

class Settings extends Component
{
    public $is_enabled = true;
    public $minimum_order_amount = 25.00;
    public $service_fee_amount = 9.00;
    public $description = '';
    public $settingId;

    public function mount()
    {
        abort_unless(auth()->user()->can('service_fee_settings'), 403);;
        $setting = SettingsServiceFee::first();

        if ($setting) {
            $this->settingId = $setting->id;
            $this->is_enabled = $setting->is_enabled;
            $this->minimum_order_amount = $setting->minimum_order_amount;
            $this->service_fee_amount = $setting->service_fee_amount;
            $this->description = $setting->description;
        }
    }

    public function save()
    {
        $this->validate([
            'is_enabled' => 'required|boolean',
            'minimum_order_amount' => 'required|numeric|min:0',
            'service_fee_amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        SettingsServiceFee::updateOrCreate(
            ['id' => $this->settingId],
            [
                'is_enabled' => $this->is_enabled,
                'minimum_order_amount' => $this->minimum_order_amount,
                'service_fee_amount' => $this->service_fee_amount,
                'description' => $this->description,
            ]
        );

        session()->flash('success', 'تم حفظ إعدادات رسوم الخدمة بنجاح!');
    }

    public function toggleEnabled()
    {
        $this->is_enabled = !$this->is_enabled;
    }

    public function render()
    {
        return view('livewire.admin.service-fee.settings')
            ->layout('components.layouts.admin-dashboard');
    }
}
