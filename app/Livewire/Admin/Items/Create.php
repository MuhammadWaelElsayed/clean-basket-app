<?php

namespace App\Livewire\Admin\Items;

use Livewire\Component;
use App\Models\Service;
use App\Models\Item;
use App\Models\ServiceType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use Livewire\TemporaryUploadedFile;
use App\Models\OrderPriority;

class Create extends Component
{
    use WithFileUploads;

    public $name = '';
    public $name_ar = '';
    public $price = null;
    public $description = '';
    public $description_ar = '';
    public $image;
    public $service_id = [];
    // متغيرات للخدمات المتعددة
    public $selectedServices = [];
    public $servicePrices = [];
    public $serviceDiscountPrices = [];

    public $order_priority_id = '';

    public $orderPriorities = [];

    public function mount()
    {
        abort_unless(auth()->user()->can('create_item'), 403);

        $this->orderPriorities = OrderPriority::all();
        $this->selectedServices = [];
        $this->servicePrices = [];
        $this->serviceDiscountPrices = [];
        $this->order_priority_id = '';
        $this->service_id = [];
    }

    public function render()
    {
        $services = Service::whereNull('deleted_at')->select('name as label', 'id as value')->get()->toArray();
        $serviceTypes = ServiceType::all();

        return view('livewire.admin.items.create', compact('services', 'serviceTypes'))->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {
        $this->validateOnly($field, [
            "name" => "required|max:240",
            "service_id" => "required|array|min:1",
            "service_id.*" => "exists:services,id",
            "selectedServices" => "required|array|min:1",
            "selectedServices.*" => "exists:service_types,id",
            "servicePrices.*" => "required|numeric|min:0",
            "serviceDiscountPrices.*" => "nullable|numeric|min:0",
            "order_priority_id" => "required|exists:order_priorities,id",
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

    public function store()
    {
        $this->validate([
            "name" => "required|max:240",
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
            $data = [
                "name" => $this->name,
                "name_ar" => $this->name_ar,
                "price" => $this->price,
                "service_id" => $this->service_id[0] ?? 1, // استخدام أول خدمة مختارة أو قيمة افتراضية
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

            $item = Item::create($data);

            // إضافة Service Categories المتعددة
            if (!empty($this->service_id)) {
                $item->serviceCategories()->attach($this->service_id);
            }

            // إضافة الخدمات المختارة مع الأولوية المختارة
            $attachedServices = [];
            foreach ($this->selectedServices as $serviceId) {
                $pivotData = [
                    'price' => $this->servicePrices[$serviceId],
                    'discount_price' => !empty($this->serviceDiscountPrices[$serviceId]) ? $this->serviceDiscountPrices[$serviceId] : null,
                    'order_priority_id' => $this->order_priority_id,
                ];
                $item->services()->attach($serviceId, $pivotData);

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

            $message = "Item '{$this->name}' Added Successfully with {$categoriesCount} Categories ({$categoriesList}) and {$servicesCount} Services ({$servicesList})";

            $this->dispatch('success', $message);
            return $this->redirectRoute('admin.items', navigate: true);

        } catch (\Exception $e) {
            $this->dispatch('error', 'Error creating item: ' . $e->getMessage());
        }
    }

    public function updatedFile(TemporaryUploadedFile $file)
    {
        $this->image = $file;
    }

    /**
     * دالة للتحقق من البيانات المحفوظة (يمكن استخدامها للتطوير)
     */
    public function verifySavedData($itemId)
    {
        $item = Item::with('services')->find($itemId);

        if (!$item) {
            return "Item not found!";
        }

        $servicesCount = $item->services->count();
        $servicesList = $item->services->pluck('name')->implode(', ');

        return [
            'item_name' => $item->name,
            'services_count' => $servicesCount,
            'services_list' => $servicesList,
            'services_details' => $item->services->map(function($service) {
                return [
                    'name' => $service->name,
                    'price' => $service->pivot->price,
                    'discount_price' => $service->pivot->discount_price,
                    'order_priority_id' => $service->pivot->order_priority_id,
                ];
            })
        ];
    }
}
