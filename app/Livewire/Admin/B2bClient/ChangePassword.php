<?php

namespace App\Livewire\Admin\B2bClient;

use Livewire\Component;
use App\Models\B2bClient;
use Illuminate\Support\Facades\Hash;

class ChangePassword extends Component
{
    public $clientId;
    public $client;

    public $new_password = '';
    public $new_password_confirmation = '';
    public $revoke_all_tokens = false;

    public function mount($id)
    {
        abort_unless(auth()->user()->can('manage_b2b_clients'), 403);

        $this->clientId = $id;
        $this->client = B2bClient::findOrFail($id);
    }

    public function render()
    {
        return view('livewire.admin.b2b-client.change-password')
            ->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {
        $this->validateOnly($field, [
            'new_password' => 'required|min:8|confirmed',
        ]);
    }

    public function changePassword()
    {
        $validated = $this->validate([
            'new_password' => 'required|min:8|confirmed',
        ]);

        $this->client->update([
            'password' => Hash::make($validated['new_password'])
        ]);

        // Optionally revoke all tokens
        if ($this->revoke_all_tokens) {
            $this->client->tokens()->delete();
        }

        $this->dispatch('success', 'Password changed successfully!');
        return $this->redirectRoute('b2b-clients.index', navigate: true);
    }
}
