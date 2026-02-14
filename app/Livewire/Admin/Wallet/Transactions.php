<?php

namespace App\Livewire\Admin\Wallet;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WalletTransaction;
use Carbon\Carbon;


class Transactions extends Component
{
    use WithPagination;

    public $search = '';
    public $daterange = '';
    public $type = '';
    public $export = false;
    protected $transactions = [];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        abort_unless(auth()->user()->can('wallet_transactions'), 403);
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    public function updatingDaterange()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = WalletTransaction::with('wallet.user','userPackage.package')->orderBy('id', 'desc');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                // البحث عن المستخدم باسمه أو بريده الإلكتروني
                $q->whereHas('wallet.user', function ($userQuery) {
                    $userQuery->where('first_name', 'like', '%' . $this->search . '%')
                             ->orWhere('last_name', 'like', '%' . $this->search . '%')
                             ->orWhere('email', 'like', '%' . $this->search . '%')
                             ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%']);
                })
                // البحث عن Transaction ID
                ->orWhere('transaction_id', 'like', '%' . $this->search . '%')
                // البحث عن Related Order ID مباشرة
                ->orWhere('related_order_id', $this->search)
                // البحث في طلب مرتبط إذا كان موجود
                ->orWhereHas('order', function ($orderQuery) {
                    $orderQuery->where('id', $this->search);
                });
            });
        }

        if (!empty($this->type)) {
            $query->where('type', $this->type);
        }

        if (!empty($this->daterange)) {
            $dates = explode(' to ', $this->daterange);
            $start = Carbon::parse($dates[0])->startOfDay();
            $end = isset($dates[1]) ? Carbon::parse($dates[1])->endOfDay() : $start;
            $query->whereBetween('created_at', [$start, $end]);
        }

        $this->transactions = $query->paginate(15);

        return view('livewire.admin.wallet.transactions.index', [
            'transactions' => $this->transactions
        ])->layout('components.layouts.admin-dashboard');
    }

    public function clearFilter()
    {
        $this->search = '';
        $this->daterange = '';
        $this->type = '';
    }


}
