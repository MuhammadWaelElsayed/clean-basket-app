<?php

namespace App\Livewire\Admin\IntegrationToken;

use Livewire\Component;
use App\Models\IntegrationToken;
use Livewire\WithPagination;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.admin-dashboard')]
class Show extends Component
{
    use WithPagination;

    public $aId = '';
    public $dId = '';
    public $status = '';
    protected $tokens = [];
    public $search = '';
    public $export = false;
    public $daterange = '';
    public $providerFilter = '';

    // Token view modal properties
    public $selectedToken = '';
    public $selectedTokenName = '';
    public $selectedTokenProvider = '';
    public $selectedTokenHint = '';
    public $selectedTokenScopes = [];

    public $listeners = ['submit-active' => 'submitActive', 'del-item' => 'delToken'];

    public function mount()
    {
        abort_unless(auth()->user()->can('integration_tokens'), 403);
    }
    public function render()
    {
        $tokens = IntegrationToken::orderBy('id', 'desc');

        if ($this->search !== '') {
            $tokens->where(function($query) {
                $query->where('name', 'LIKE', '%' . $this->search . '%')
                      ->orWhere('provider', 'LIKE', '%' . $this->search . '%')
                      ->orWhere('token_hint', 'LIKE', '%' . $this->search . '%');
            });
        }

        if ($this->providerFilter !== '') {
            $tokens->where('provider', $this->providerFilter);
        }

        if ($this->daterange !== '') {
            $date = explode(' to ', $this->daterange);
            $startDate = date('Y-m-d', strtotime($date[0]));
            if (isset($date[1])) {
                $endDate = date('Y-m-d', strtotime($date[1]));
                $tokens->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
            }
        }

        if ($this->export == true) {
            $tokensData = $tokens->get();
            $this->export($tokensData);
        }

        $this->tokens = $tokens->paginate(15);

        return view('livewire.admin.integration-token.index', [
            'tokens' => $this->tokens
        ]);
    }

    public function updated($field)
    {
        $tokens = IntegrationToken::orderBy('id', 'desc')->paginate(15);
    }

    public function clearFilter()
    {
        $this->search = '';
        $this->daterange = '';
        $this->providerFilter = '';
    }

    public function submitActive()
    {
        $status = ($this->status == 1) ? 0 : 1;
        IntegrationToken::findOrFail($this->aId)->update(['is_active' => $status]);
        $this->dispatch('success', 'Token Status Updated Successfully!');
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

    public function delToken()
    {
        IntegrationToken::findOrFail($this->dId)->delete();
        $this->dispatch('success', 'Token Deleted Successfully!');
    }

    public function revokeToken($id)
    {
        IntegrationToken::findOrFail($id)->update([
            'is_active' => false,
            'revoked_at' => now()
        ]);
        $this->dispatch('success', 'Token Revoked Successfully!');
    }

    public function getProviders()
    {
        return IntegrationToken::distinct()->pluck('provider')->toArray();
    }

    public function export($tokens)
    {
        // يمكن إضافة منطق التصدير هنا
        $this->export = false;
    }

    public function viewToken($id)
    {
        $token = IntegrationToken::findOrFail($id);

        $this->selectedTokenName = $token->name;
        $this->selectedTokenProvider = $token->provider;
        $this->selectedTokenHint = $token->token_hint;
        $this->selectedTokenScopes = $token->scopes ?? [];

        // Check if token was created recently (within last 5 minutes) and might be in session
        $recentlyCreated = $token->created_at->diffInMinutes(now()) < 5;

        if ($recentlyCreated && session()->has('recently_created_token_' . $token->id)) {
            $this->selectedToken = session('recently_created_token_' . $token->id);
        } else {
            // Check if encrypted token exists in database
            if ($token->encrypted_token) {
                try {
                    $this->selectedToken = decrypt($token->encrypted_token);
                } catch (\Exception $e) {
                    $this->selectedToken = 'Token decryption failed.';
                }
            } else {
                $this->selectedToken = 'Token not available.';
            }
        }

        // Show modal
        $this->dispatch('show-token-modal');
    }
}
