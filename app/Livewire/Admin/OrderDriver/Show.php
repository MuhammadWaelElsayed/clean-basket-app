<?php

namespace App\Livewire\Admin\OrderDriver;

use App\Models\OrderDriver;
use Livewire\Component;

class Show extends Component
{
    public OrderDriver $orderDriver;

    public function mount($id)
    {
        $this->orderDriver = OrderDriver::with(['driver', 'order', 'vendor'])
            ->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.admin.order-driver.show')
            ->layout('components.layouts.admin-dashboard');
    }
}
