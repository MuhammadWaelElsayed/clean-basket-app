<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\UserAddress;
use App\Models\BasketInventory;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class Inventory extends Component
{
    public $search='';
    public $daterange='';
    public $showModal=false;
    public $desc='';
    public $quantity='';

    public $listeners = ['approve', 'reject'];

    public function mount()
    {
        abort_unless(auth()->user()->can('basket_inventory'), 403);
    }
    public function render()
    {

        $inventory=BasketInventory::latest();

        if($this->search!==''){
            $inventory->where('desc','LIKE', '%'.$this->search.'%');
        }

        if($this->daterange!==''){
            $date= explode(' to ',$this->daterange);
            $startDate=date('Y-m-d',strtotime($date[0]));
            if(isset($date[1])){
                $endDate=date('Y-m-d',strtotime($date[1]));
                $inventory->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
            }
        }
        $inventory= $inventory->paginate(15);

        $stats['total']=BasketInventory::sum('quantity');
        $stats['delivered']=UserAddress::where('basket_status','Delivered')->count();
        $stats['available']=  $stats['total'] - $stats['delivered'];
        // dd($inventory);
        return view('livewire.admin.inventory.index',compact('inventory','stats'))
        ->layout('components.layouts.admin-dashboard');
    }

    public function clearFilter() {
        $this->search='';
        $this->daterange='';
    }

    public function saveData() {
        $this->validate([
            "quantity"=>"required|gt:0"
        ]);
        $address=BasketInventory::create([
            "quantity" => $this->quantity,
            "desc" => $this->desc,
        ]);
        $this->showModal=false;
        $this->dispatch('success', 'Invetory Added!');

    }



}
