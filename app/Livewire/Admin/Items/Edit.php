<?php

namespace App\Livewire\Admin\Items;

use Livewire\Component;
use App\Models\Service;
use App\Models\Item;
use App\Models\ServiceType;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use App\Models\OrderPriority;
use App\Models\ItemServiceType;

class Edit extends Component
{
    use WithFileUploads;

    public $name = '';
    public $name_ar = '';
    public $price = '';
    public $description = '';
    public $description_ar = '';
    public $itemImage;
    public $image;
    public $imageUrl;
    public $service_id = [];

    public $itemId;

    // متغيرات جديدة للخدمات المتعددة
    public $selectedServices = [];
    public $servicePrices = [];
    public $serviceDiscountPrices = [];

    public $servicePriorityRows = [];
    public $orderPriorities = [];
    public $order_priority_id = '';

    public function mount($id)
    {
        abort_unless(auth()->user()->can('update_item'), 403);

        $this->itemId = $id;
        $item = Item::with(['services', 'serviceCategories'])->find($this->itemId);

        $this->name = $item->name;
        $this->name_ar = $item->name_ar;
        $this->service_id = $item->serviceCategories->pluck('id')->toArray(); // تحميل Service Categories المحفوظة
        $this->price = $item->price;
        $this->description = $item->description;
        $this->description_ar = $item->description_ar;
        $this->itemImage = $item->image;
        $this->imageUrl = $item->image;

        $this->orderPriorities = OrderPriority::all();
        $this->selectedServices = $item->services->pluck('id')->toArray();
        $this->servicePrices = [];
        $this->serviceDiscountPrices = [];
        $this->order_priority_id = '';
        // تحميل الأسعار وسعر مخفض لكل خدمة
        foreach ($item->services as $service) {
            $this->servicePrices[$service->id] = $service->pivot->price;
            $this->serviceDiscountPrices[$service->id] = $service->pivot->discount_price;
            if ($this->order_priority_id == '' && isset($service->pivot->order_priority_id)) {
                $this->order_priority_id = $service->pivot->order_priority_id;
            }
        }
    }

    public function render()
    {
        $services = Service::whereNull('deleted_at')->select('name as label', 'id as value')->get()->toArray();
        $serviceTypes = ServiceType::all();
        $item = Item::find($this->itemId);

        return view('livewire.admin.items.edit', compact('services', 'serviceTypes'))->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {
        $this->validateOnly($field, [
            "name" => "required",
            "service_id" => "required|array|min:1",
            "service_id.*" => "exists:services,id",
            "selectedServices" => "required|array|min:1",
            "selectedServices.*" => "exists:service_types,id",
            "servicePrices.*" => "required|numeric|min:0",
            "serviceDiscountPrices.*" => "nullable|numeric|min:0",
            "price"=>"numeric|min:1",
        ]);
    }

    public function updatedSelectedServices()
    {
        // إعادة تعيين الأسعار عند تغيير الخدمات المختارة
        $this->servicePrices = [];
        $this->serviceDiscountPrices = [];

        foreach ($this->selectedServices as $serviceId) {
            if (!isset($this->servicePrices[$serviceId])) {
                $this->servicePrices[$serviceId] = '';
            }
            if (!isset($this->serviceDiscountPrices[$serviceId])) {
                $this->serviceDiscountPrices[$serviceId] = '';
            }
        }
    }

    public function addServicePriorityRow()
    {
        $this->servicePriorityRows[] = ['service_type_id' => '', 'order_priority_id' => '', 'price' => '', 'discount_price' => ''];
    }

    public function removeServicePriorityRow($index)
    {
        unset($this->servicePriorityRows[$index]);
        $this->servicePriorityRows = array_values($this->servicePriorityRows);
    }

    public function updateData()
    {
        $this->validate([
            "name" => "required",
            "service_id" => "required|array|min:1",
            "service_id.*" => "exists:services,id",
            "selectedServices" => "required|array|min:1",
            "selectedServices.*" => "exists:service_types,id",
            "servicePrices.*" => "required|numeric|min:0",
            "serviceDiscountPrices.*" => "nullable|numeric|min:0",
            "order_priority_id" => "required|exists:order_priorities,id",
            "price"=>"numeric|min:1",
        ]);

        try {
            $item = Item::findOrFail($this->itemId);

            $data = [
                "name" => $this->name,
                "name_ar" => $this->name_ar,
                "price" => ($this->price=='')?null:$this->price,
                "service_id" => !empty($this->service_id) ? $this->service_id[0] : $item->service_id, // استخدام أول خدمة مختارة أو القيمة الحالية
                "description" => $this->description,
                "description_ar" => $this->description_ar,
            ];

            if ($this->image) {
                $this->validate([
                    "image" => "image|mimes:jpeg,png,jpg,gif|max:2048",
                ]);

                $imageName = date('ymdhis') . "_item." . $this->image->getClientOriginalExtension();
                $this->image->storeAs('public/uploads', $imageName);
                $data['image'] = $imageName;
            }

            $item->update($data);

            // تحديث Service Categories المتعددة
            if (!empty($this->service_id)) {
                $item->serviceCategories()->sync($this->service_id);
            } else {
                $item->serviceCategories()->detach();
            }

            // حذف التركيبات القديمة
            ItemServiceType::where('item_id', $this->itemId)->delete();

            // إضافة الخدمات المختارة مع الأولوية المختارة
            $attachedServices = [];
            foreach ($this->selectedServices as $serviceId) {
                ItemServiceType::create([
                    'item_id' => $this->itemId,
                    'service_type_id' => $serviceId,
                    'order_priority_id' => $this->order_priority_id,
                    'price' => $this->servicePrices[$serviceId],
                    'discount_price' => !empty($this->serviceDiscountPrices[$serviceId]) ? $this->serviceDiscountPrices[$serviceId] : null,
                ]);

                // الحصول على اسم الخدمة للتأكيد
                $serviceType = ServiceType::find($serviceId);
                $attachedServices[] = $serviceType->name;
            }

            // رسالة تأكيد مفصلة
            $servicesCount = count($this->selectedServices);
            $servicesList = implode(', ', $attachedServices);

            // إضافة Service Categories للرسالة
            $categoriesCount = count($this->service_id);
            $categoriesList = '';
            if (!empty($this->service_id)) {
                $categories = Service::whereIn('id', $this->service_id)->pluck('name')->toArray();
                $categoriesList = implode(', ', $categories);
            }

            $message = "Item '{$this->name}' Updated Successfully with {$categoriesCount} Categories ({$categoriesList}) and {$servicesCount} Services ({$servicesList})";

            $this->dispatch('success', $message);
            return $this->redirectRoute('admin.items', navigate: true);

        } catch (\Exception $e) {
            $this->dispatch('error', 'Error updating item: ' . $e->getMessage());
        }
    }
}
