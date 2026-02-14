<?php

namespace App\Livewire\Partner\Order;

use Livewire\Component;
use App\Models\Service;
use App\Models\Order;
use App\Models\Item;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use App\Models\Notification;

class Edit extends Component
{
    use WithFileUploads;

   
    public $items=[
        ["item_id"=>'','type'=>'Press','price'=>'','quantity'=>'1','sub_total'=>null]
    ];
    public $orderId;
    public $order;
    public $row=1;
    public $grand_total=0;
    public $statuses = [  ];
    public $listeners = ['save', 'getPrice' ];


    public function mount($id)
    {
        $this->orderId = $id;
        $this->order=Order::with('orderItems.item')->find($this->orderId);
        // dd($this->order->orderItems);
        $i=0;
        foreach($this->order->orderItems as $item){
            $this->items[$i]=[
                "item_id" => $item->item_id,
                "type" => $item->service_type,
                "price" => $item->price,
                "quantity" => $item->quantity,
                "sub_total" => $item->total_price,
            ];
            // $this->grand_total+= $item->total_price;
            $i++;
        }

    }

    public function render()
    {
        $pricing_items=Item::select('id as value','name as label')->where(['status'=>1])->whereNull('deleted_at')->get();

        return view('livewire.partner.orders.edit',compact('pricing_items'))->layout('components.layouts.partner-dashboard');
    }



    public function getPrice($key='')
    {
        
       $item_id=$this->items[$key]['item_id'];
       $quantity=(float)$this->items[$key]['quantity'];
       $type=$this->items[$key]['type'];
       $item= Item::find($item_id);
       if($type=="Press"){
        $price = $item->press_price;
       }else{
        $price= $item->wash_price;
       }
       $this->items[$key]['price']=$price;
        if($quantity==0){
            $this->items[$key]['sub_total']=$price;
        }else{
            $this->items[$key]['sub_total']=$price*$quantity;
        }
        // $this->grand_total+=  $this->items[$key]['sub_total'];

        // dd($this->items);
    }

    public function add()
    {
        $this->items[]= ["item_id"=>'',"type"=>'Press','price'=>'','quantity'=>'1','sub_total'=>null];
        $this->dispatch('newSelect2', count($this->items));
    //    dd($this->items);
    }


    public function remove($key)
    {
       unset($this->items[$key]);
    }

    public function save()
    {
        // dd($this->items);
        OrderItem::where(['order_id'=>$this->orderId])->delete();
        $grand_total=0;
       foreach ($this->items as $item) {
        OrderItem::create([
            "order_id"=> $this->orderId,
            "item_id"=> $item['item_id'],
            "service_type"=> $item['type'],
            "quantity"=> $item['quantity'],
            "price"=> $item['price'],
        ]);
        $grand_total+=$item['quantity']* $item['price'];
       }
        $order= Order::with('user')->find($this->orderId);
       $order->update([
        "grand_total" => $grand_total,
        "status" => 'Processing',
       ]);
    //    Controller::sendNotifications($data,'user');
      
       
        $this->dispatch('success', 'Order Items Updated Successfully!');
        return redirect('partner/order-details/'.$this->orderId);
        
    }

    


}
