<?php

namespace App\Livewire\Admin\Wallet;

use App\Http\Controllers\Controller;
use Livewire\Component;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ManualCharge extends Component
{
    public $user_id;
    public $amount;
    public $description;
    // Add for VirtualSelect compatibility
    public $users;

    protected $rules = [
        'user_id' => 'required|exists:users,id',
        'amount' => 'required|numeric|min:0.01',
        'description' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        abort_unless(auth()->user()->can('wallet_manual_charge'), 403);
    }
    public function submit()
    {
        $this->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);


        $user = User::findOrFail($this->user_id);

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $this->user_id],
            ['balance' => 0]
        );

        $wallet->increment('balance', $this->amount);

        $transactionId = 'ADM-' . now()->format('YmdHis') . '-' . Str::random(4);

        WalletTransaction::create([
            'transaction_id' => $transactionId,
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'amount' => $this->amount,
            'source' => 'Refund',
            'description' => $this->description ?? 'Manual charge',
        ]);

        Controller::sendNotifications([
            "title" => "Wallet Recharged",
            "title_ar" => "تم شحن المحفظة",
            "message" => "An amount of {$this->amount} SAR has been added to your wallet.",
            "message_ar" => "تم إضافة مبلغ {$this->amount} ريال إلى محفظتك.",
            "user" => $user,
        ], "user");

        $this->reset(['user_id', 'amount', 'description']);
        session()->flash('success', 'Balance charged successfully.');
    }

    public function render()
    {
        $users_arr = User::whereNull('deleted_at')
            ->where('status', 1)
            ->get()
            ->map(function ($user) {
                return [
                    'label' => trim($user->first_name . ' ' . $user->last_name . ' - ' . $user->phone),
                    'value' => $user->id
                ];
            })
            ->toArray();
        // Keep old $users for fallback (if needed elsewhere)
        $users = \App\Models\User::select('id', 'first_name', 'last_name', 'email')->get();
        return view('livewire.admin.wallet.manual-charge', compact('users', 'users_arr'))
            ->layout('components.layouts.admin-dashboard');
    }
}
