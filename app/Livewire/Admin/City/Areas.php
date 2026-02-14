<?php

namespace App\Livewire\Admin\City;

use Livewire\Component;
use App\Models\Area;
use App\Models\City;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;

class Areas extends Component
{
    use WithFileUploads;

    public $name='';
    public $cityId;
    public $cityName;
    public $areas=[
        [
            "id"=>'',
            "name"=>'',
            "name_ar"=>'',
        ]
    ];

    public function mount($id)
    {
        abort_unless(auth()->user()->can('update_city'), 403);

        $this->cityId = $id;
        $city=City::with('areas')->find($this->cityId)->toArray();
        $this->cityName=$city['name'];
        if(count($city['areas'])>0){
            $this->areas=$city['areas'];
        }
    }

    public function render()
    {

        return view('livewire.admin.cities.areas')->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {
        $this->validateOnly($field,[
            "name"=>"required ",
        ]);

    }

    public function updateData()
    {
        $this->validate([
            "areas.*.name"=>"required ",
        ]);

        foreach ($this->areas as $area) {
            //Update
            if($area['id']!=''){
                $data=[
                    "name" => $area['name'],
                    "name_ar" => $area['name_ar'],
                    "city_id" => $this->cityId,
                ];
                Area::findOrFail($area['id'])->update($data);
            }
            //Create New
            else{
                $data=[
                    "name" => $area['name'],
                    "name_ar" => $area['name_ar'],
                    "city_id" => $this->cityId,
                ];
                Area::create($data);
            }

       }

        $this->dispatch('success', 'City Areas Updated Successfully!');
        return $this->redirectRoute('admin.cities', navigate: true);


    }

    public function addNew()
    {
        $this->areas[]=[
            "id"=>'',
            "name"=>'',
            "name_ar"=>'',
        ];
    }

    public function removeRow($key, $id='')
    {

        if($id!=''){
            Area::findOrFail($id)->delete();
        }
        unset($this->areas[$key]);
        $this->areas= array_values($this->areas);
    }


}
