<?php

namespace App\Livewire\Admin\Vendor;

use Livewire\Component;
use App\Models\Vendor;
use App\Models\VendorWorkingHours;
use Livewire\WithPagination;

class SingleVendorWorkingHours extends Component
{
    use WithPagination;

    public $vendor_id = '';
    public $vendor = null;
    public $workingHours = [];
    public $daysOfWeek = [];
    public $showForm = false;
    public $editingId = null;

    // Form fields
    public $day_of_week = '';
    public $day_en = '';
    public $day_ar = '';
    public $open_time = '';
    public $close_time = '';
    public $is_closed = false;

    protected $rules = [
        'day_of_week' => 'required|integer|between:0,6',
        'day_en' => 'required|string',
        'day_ar' => 'required|string',
        'open_time' => 'nullable|date_format:H:i|required_if:is_closed,false',
        'close_time' => 'nullable|date_format:H:i|required_if:is_closed,false',
        'is_closed' => 'boolean'
    ];

    protected function getValidationAttributes()
    {
        return [
            'day_of_week' => 'اليوم',
            'open_time' => 'وقت الفتح',
            'close_time' => 'وقت الإغلاق',
        ];
    }

    public function mount($id)
    {
        $this->vendor_id = $id;
        $this->vendor = Vendor::findOrFail($id);
        $this->daysOfWeek = VendorWorkingHours::getDaysOfWeek();
        $this->loadWorkingHours();
    }

    protected $layout = 'components.layouts.app';

    public function render()
    {
        return view('livewire.admin.vendors.single-vendor-working-hours', [
            'workingHours' => $this->workingHours,
            'vendor' => $this->vendor,
            'daysOfWeek' => $this->daysOfWeek
        ])->layout('components.layouts.admin-dashboard');
    }

    public function loadWorkingHours()
    {
        $this->workingHours = VendorWorkingHours::where('vendor_id', $this->vendor_id)
            ->orderBy('day_of_week')
            ->get();
    }

    public function showAddForm()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function showEditForm($id)
    {
        $workingHour = VendorWorkingHours::findOrFail($id);

        $this->editingId = $id;
        $this->day_of_week = $workingHour->day_of_week;
        $this->day_en = $workingHour->day_en;
        $this->day_ar = $workingHour->day_ar;
        $this->open_time = $workingHour->open_time ? $workingHour->open_time->format('H:i') : '';
        $this->close_time = $workingHour->close_time ? $workingHour->close_time->format('H:i') : '';
        $this->is_closed = $workingHour->is_closed;

        $this->showForm = true;
    }

    public function daySelected()
    {
        if ($this->day_of_week !== '') {
            $day = $this->daysOfWeek[$this->day_of_week];
            $this->day_en = $day['en'];
            $this->day_ar = $day['ar'];
        }
    }

    public function updated($field)
    {
        // Clear validation errors when fields are updated
        $this->resetErrorBag($field);

        // Validate time logic when either time field is updated
        if (in_array($field, ['open_time', 'close_time']) && !$this->is_closed) {
            if ($this->open_time && $this->close_time && $this->open_time >= $this->close_time) {
                $this->addError('close_time', 'وقت الإغلاق يجب أن يكون بعد وقت الفتح');
            }
        }
    }

    public function save()
    {
        $this->validate();

        // Additional validation for time logic
        if (!$this->is_closed && $this->open_time && $this->close_time) {
            if ($this->open_time >= $this->close_time) {
                $this->addError('close_time', 'وقت الإغلاق يجب أن يكون بعد وقت الفتح');
                return;
            }
        }

        // Check if working hours already exist for this vendor and day
        $existing = VendorWorkingHours::where('vendor_id', $this->vendor_id)
            ->where('day_of_week', $this->day_of_week)
            ->when($this->editingId, function($query) {
                return $query->where('id', '!=', $this->editingId);
            })
            ->first();

        if ($existing) {
            $this->dispatch('error', 'أوقات العمل لهذا اليوم موجودة مسبقاً');
            return;
        }

        $data = [
            'vendor_id' => $this->vendor_id,
            'day_of_week' => $this->day_of_week,
            'day_en' => $this->day_en,
            'day_ar' => $this->day_ar,
            'open_time' => $this->is_closed ? null : $this->open_time,
            'close_time' => $this->is_closed ? null : $this->close_time,
            'is_closed' => $this->is_closed,
        ];

        if ($this->editingId) {
            VendorWorkingHours::findOrFail($this->editingId)->update($data);
            $this->dispatch('success', 'تم تحديث أوقات العمل بنجاح');
        } else {
            VendorWorkingHours::create($data);
            $this->dispatch('success', 'تم إضافة أوقات العمل بنجاح');
        }

        $this->resetForm();
        $this->showForm = false;
        $this->loadWorkingHours();
    }

    public function delete($id)
    {
        VendorWorkingHours::findOrFail($id)->delete();
        $this->dispatch('success', 'تم حذف أوقات العمل بنجاح');
        $this->loadWorkingHours();
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->day_of_week = '';
        $this->day_en = '';
        $this->day_ar = '';
        $this->open_time = '';
        $this->close_time = '';
        $this->is_closed = false;
    }

    public function cancel()
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function backToPartners()
    {
        return redirect()->route('admin.partners');
    }
}
