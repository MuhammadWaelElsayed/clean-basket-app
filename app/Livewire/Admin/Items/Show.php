<?php

namespace App\Livewire\Admin\Items;

use Livewire\Component;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use League\Csv\Writer;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public $aId='';
    public $dId='';
    public $status='';
    protected $items=[];
    public $search='';
    public $export=false;
    public $daterange='';

    public $listeners = ['submit-active' => 'submitActive', 'del-item'=>'delItem'];

    public function render()
    {
        abort_unless(auth()->user()->can('list_item'), 403);

        $items=Item::with(['service', 'services', 'serviceCategories'])->orderBy('id','desc')->whereNull('deleted_at');
            if($this->search!==''){
                $items->where('name','LIKE', '%'.$this->search.'%')->orWhereHas('service', function($q){
                    $q->where('name','LIKE', '%'.$this->search.'%');
                });
            }
            if($this->daterange!==''){
                // dd($this->daterange);
                $date= explode(' to ',$this->daterange);
                $startDate=date('Y-m-d',strtotime($date[0]));
                if(isset($date[1])){
                    $endDate=date('Y-m-d',strtotime($date[1]));
                    $items->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
                }
            }

        $this->items=$items->paginate(15);

        return view('livewire.admin.items.index',[
            'items' => $this->items
        ])->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {

        $items=Item::with('service')->orderBy('id','desc')->whereNull('deleted_at')->paginate(15);

    }


    public function clearFilter()
    {
        $this->search='';
        $this->daterange='';

    }




    public function submitActive()
    {
        $status=($this->status==1)?0:1;
       Item::findOrFail($this->aId)->update(['status'=>$status]);
        $this->dispatch('success', 'Item Updated Successfully!');
    }

    public function activeInactive($id,$status)
    {
        $this->aId=$id;
        $this->status=$status;
    }

    public function setDel($id)
    {
        $this->dId=$id;
    }

    public function delItem()
    {
        Item::findOrFail($this->dId)->delete();
        $this->dispatch('success', 'Item Deleted Successfully!');

    }
    public function exportData()
    {
        $orders=Item::with('service')->orderBy('id','desc')->whereNull('deleted_at');

        if($this->search!==''){
            $orders->where('name','LIKE', '%'.$this->search.'%')->orWhereHas('service', function($q){
                $q->where('name','LIKE', '%'.$this->search.'%');
            });
        }
        if($this->daterange!==''){
            // dd($this->daterange);
            $date= explode(' to ',$this->daterange);
            $startDate=date('Y-m-d',strtotime($date[0]));
            if(isset($date[1])){
                $endDate=date('Y-m-d',strtotime($date[1]));
                $orders->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
            }
        }
            $data=$orders->get();
        // Define your data
        $columns = ['Image', 'Name','Name Ar', 'Service','Pressing Price','Description','Created at','Status'];

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        // Add headers to the CSV
        $csv->insertOne($columns);
        foreach ($data as $row) {
            $single=[$row->image,$row->name,$row->name_ar,$row->service->name,
            env('CURRENCY').$row->price,$row->description,
            $row->created_at,($row->status==1)?'Active':'Inactive'
        ];
            $csv->insertOne($single);
        }
        $filename = 'items_' . date('Y-m-d')  . '.csv';
        // Open a temporary file for writing
        $file = fopen('php://temp', 'w');
        fwrite($file, $csv->getContent());
        rewind($file);
        // Set the appropriate headers for downloading the file
        $headers = [    'Content-Type' => 'text/csv',    'Content-Disposition' => 'attachment; filename="' . $filename . '"',];

        return response()->streamDownload(function () use ($file) {
            fpassthru($file);
        }, $filename, $headers);

    }

}
