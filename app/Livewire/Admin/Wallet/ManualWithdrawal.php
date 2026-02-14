<?php

namespace App\Livewire\Admin\Wallet;

use App\Http\Controllers\Controller;
use Livewire\Component;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ManualWithdrawal extends Component
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
        abort_unless(auth()->user()->can('wallet_manual_withdraw'), 403);
    }
    public function submit()
    {
        $this->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($this->user_id);

        $wallet = Wallet::where('user_id', $this->user_id)->first();

        if (!$wallet) {
            session()->flash('error', 'User does not have a wallet.');
            return;
        }

        if ($wallet->balance < $this->amount) {
            session()->flash('error', 'Insufficient balance. Current balance: ' . $wallet->balance . ' ' . env('CURRENCY', 'SAR'));
            return;
        }

        $wallet->decrement('balance', $this->amount);

        $transactionId = 'WDR-' . now()->format('YmdHis') . '-' . Str::random(4);

        WalletTransaction::create([
            'transaction_id' => $transactionId,
            'wallet_id' => $wallet->id,
            'type' => 'debit',
            'amount' => $this->amount,
            'source' => 'Manual Withdrawal',
            'description' => $this->description ?? 'Manual withdrawal by admin',
        ]);

        Controller::sendNotifications([
            "title" => "Wallet Withdrawal",
            "title_ar" => "سحب من المحفظة",
            "message" => "An amount of {$this->amount} SAR has been deducted from your wallet.",
            "message_ar" => "تم خصم مبلغ {$this->amount} ريال من محفظتك.",
            "user" => $user,
        ], "user");

        $this->reset(['user_id', 'amount', 'description']);
        session()->flash('success', 'Amount withdrawn successfully.');
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
        return view('livewire.admin.wallet.manual-withdrawal', compact('users', 'users_arr'))
            ->layout('components.layouts.admin-dashboard');
    }
}
