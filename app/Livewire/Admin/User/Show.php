<?php

namespace App\Livewire\Admin\User;

use Livewire\Component;
use App\Models\User;
use App\Models\Area;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithPagination;
use League\Csv\Writer;

class Show extends Component
{
    use WithPagination;

    public $aId = '';
    public $dId = '';
    public $status = '';
    protected $users = [];
    public $search = '';
    public $export = false;
    public $daterange = '';

    public $listeners = ['submit-active' => 'submitActive', 'del-item' => 'delUser'];

    // public function render()
    // {
    //     $users = User::with('address')->leftJoin('orders as last_order', function ($join) {
    //         $join->on('users.id', '=', 'last_order.user_id')
    //             ->whereRaw('last_order.created_at = (SELECT MAX(created_at) FROM orders WHERE user_id = users.id)');
    //     })
    //         ->select('users.*', 'last_order.created_at as last_order_at')
    //         ->orderBy('id', 'desc')->whereNull('users.deleted_at')->whereNotNull('users.phone');
    //     if ($this->search !== '') {
    //         $search = $this->search;
    //         $area = Area::where('name', 'LIKE', '%' . $search . '%')->pluck('id')->first();

    //         $users->where(function ($query) use ($search, $area) {
    //             $query->where('users.first_name', 'LIKE', '%' . $search . '%')
    //                 ->orWhere('users.email', 'LIKE', '%' . $search . '%')
    //                 ->orWhere('users.phone', 'LIKE', '%' . $search . '%')
    //                 ->orWhereHas('address', function ($q) use ($area) {
    //                     $q->where('area',  $area);
    //                 });
    //         });
    //     }
    //     if ($this->daterange !== '') {
    //         // dd($this->daterange);
    //         $date = explode(' to ', $this->daterange);
    //         $startDate = date('Y-m-d', strtotime($date[0]));
    //         if (isset($date[1])) {
    //             $endDate = date('Y-m-d', strtotime($date[1]));
    //             $users->whereDate('users.created_at', '>=', $startDate)->whereDate('users.created_at', '<=', $endDate);
    //         }
    //     }
    //     if ($this->export == true) {
    //         $users = $users->get();
    //         $this->export($users);
    //     }
    //     // dd($users->limit(5)->get());
    //     $this->users = $users->paginate(15);

    //     return view('livewire.admin.users.index', [
    //         'users' => $this->users
    //     ])->layout('components.layouts.admin-dashboard');
    // }

    public function mount()
    {
        abort_unless(auth()->user()->can('list_customer'), 403);
    }
    public function render()
    {
        $users = User::with('address')
            ->leftJoin('orders as last_order', function ($join) {
                $join->on('users.id', '=', 'last_order.user_id')
                    ->whereRaw('last_order.created_at = (SELECT MAX(created_at) FROM orders WHERE user_id = users.id)');
            })
            ->select('users.*', 'last_order.created_at as last_order_at')
            ->whereNull('users.deleted_at')
            ->whereNotNull('users.phone')
            ->orderByDesc('users.id');

        // --- Search filter ---
        if (trim($this->search) !== '') {
            $search = trim($this->search);

            // normalize phone: remove anything that's not a digit
            $normalizedPhone = preg_replace('/\D+/', '', $search);

            // get area id only if search isn't a pure phone number
            $areaId = null;
            if ($normalizedPhone === '' || !ctype_digit($normalizedPhone)) {
                $areaId = Area::where('name', 'LIKE', '%' . $search . '%')->value('id');
            }

            $users->where(function ($q) use ($search, $normalizedPhone, $areaId) {
                // name / email / phone (raw)
                $q->where('users.first_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('users.last_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $search . '%')
                    ->orWhere('users.phone', 'LIKE', '%' . $search . '%');

                // phone (normalized) e.g. matches +966, spaces, dashes
                if ($normalizedPhone !== '') {
                    $q->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(users.phone, '+',''), ' ',''), '-','') LIKE ?",
                        ['%' . $normalizedPhone . '%']
                    );
                }

                // area by id (only if we actually found a matching area)
                if (!is_null($areaId)) {
                    $q->orWhereHas('address', function ($qq) use ($areaId) {
                        $qq->where('area', $areaId);
                    });
                }
            });
        }

        // --- Date range filter ---
        if (trim($this->daterange) !== '') {
            $date = explode(' to ', $this->daterange);
            $startDate = date('Y-m-d', strtotime($date[0] ?? ''));
            if (!empty($date[1])) {
                $endDate = date('Y-m-d', strtotime($date[1]));
                $users->whereDate('users.created_at', '>=', $startDate)
                    ->whereDate('users.created_at', '<=', $endDate);
            }
        }

        // --- Export (kept same behavior) ---
        if ($this->export == true) {
            $exportRows = $users->get();
            $this->export($exportRows);
            // ملاحظة: إذا دالة export() تعمل streamDownload فالمتصفح سيبدأ التحميل.
            // لو حبيت تتجنب استمرار التنفيذ بعد التصدير، يمكنك إرجاع response من هنا.
        }

        $this->users = $users->paginate(15);

        return view('livewire.admin.users.index', [
            'users' => $this->users
        ])->layout('components.layouts.admin-dashboard');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedDaterange()
    {
        $this->resetPage();
    }


    public function updated($field)
    {
        $users = User::orderBy('id', 'desc')->whereNull('deleted_at')->paginate(15);
    }

    public function clearFilter()
    {
        $this->search = '';
        $this->daterange = '';
    }

    public function submitActive()
    {
        $status = ($this->status == 1) ? 0 : 1;
        $user = User::findOrFail($this->aId);
        $user->update(['status' => $status]);

        if ($status == 0) {
            $user->tokens()->delete();
        }
        $this->dispatch('success', 'User Updated Successfully!');
    }

    public function activeInactive($id, $status)
    {
        $this->aId = $id;
        $this->status = $status;
    }

    public function setDel($id)
    {
        $this->dId = $id;
    }

    public function delUser()
    {
        User::findOrFail($this->dId)->delete();
        $this->dispatch('success', 'User Deleted Successfully!');
    }

    public function exportData()
    {
        $users = User::withCount('orders')->orderBy('id', 'desc')->whereNull('deleted_at')->whereNotNull('phone');
        if ($this->search !== '') {
            $users->where('first_name', 'LIKE', '%' . $this->search . '%')
                ->orWhere('email', 'LIKE', '%' . $this->search . '%')
                ->orWhere('phone', 'LIKE', '%' . $this->search . '%');
        }

        if ($this->daterange !== '') {
            $date = explode(' to ', $this->daterange);
            $startDate = date('Y-m-d', strtotime($date[0]));
            if (isset($date[1])) {
                $endDate = date('Y-m-d', strtotime($date[1]));
                $users->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
            }
        }
        $data = $users->get();

        // Define your data with new columns
        $columns = ['First Name', 'Last Name', 'Email', 'Phone', 'Created at', 'Status', 'Total Orders', 'Total Orders (Excluding Specific Statuses)', 'Total Customer Payment'];

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        // Add headers to the CSV
        $csv->insertOne($columns);
        foreach ($data as $row) {
            // Calculate orders excluding specific statuses based on sorting
            // $excludedOrders = $row->orders()
            //     ->where(function($query) {
            //         $query->whereNotIn('status', ['DRAFT', 'CANCELLED'])
            //               ->where(function($q) {
            //                   $q->where('status', '!=', 'PLACED')
            //                     ->orWhere('sorting', '!=', 'vendor');
            //               });
            //     })
            //     ->get();

            // $excludedOrdersCount = $excludedOrders->count();
            // $totalCustomerPayment = $excludedOrders->sum('grand_total');

            $excludedOrdersQuery = $row->orders()
                ->whereNotIn('status', ['DRAFT', 'CANCELLED'])
                ->where(function ($q) {
                    // نأخذ كل شيء ما عدا (PLACED & vendor)
                    $q->where('status', '!=', 'PLACED')
                        ->orWhere(function ($q2) {
                            $q2->where('sorting', '!=', 'vendor')
                                ->orWhereNull('sorting'); // مهم إذا sorting قد تكون NULL
                        });
                });

            $excludedOrdersCount  = $excludedOrdersQuery->count();
            $totalCustomerPayment = $excludedOrdersQuery->sum('grand_total');


            $single = [
                $row->first_name,
                $row->last_name,
                $row->email,
                $row->phone,
                $row->created_at,
                ($row->status == 1) ? "Active" : "Inactive",
                $row->orders_count,
                $excludedOrdersCount,
                number_format($totalCustomerPayment, 2)
            ];
            $csv->insertOne($single);
        }
        $filename = 'users_' . date('Y-m-d')  . '.csv';
        // Open a temporary file for writing
        $file = fopen('php://temp', 'w');
        fwrite($file, $csv->getContent());
        rewind($file);
        // Set the appropriate headers for downloading the file
        $headers = ['Content-Type' => 'text/csv',    'Content-Disposition' => 'attachment; filename="' . $filename . '"',];

        return response()->streamDownload(function () use ($file) {
            fpassthru($file);
        }, $filename, $headers);
    }
}
