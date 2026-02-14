<?php

namespace App\Livewire\Admin\IntegrationToken;

use Livewire\Component;
use App\Models\IntegrationToken;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.admin-dashboard')]
class Create extends Component
{
    public $eId;
    public $name = '';
    public $provider = '';
    public $scopes = ['webhook:write'];
    public $is_active = true;
    public $expires_at = '';
    public $generatedToken = '';
    public $showToken = false;

    public function mount($id = null)
    {
        abort_unless(auth()->user()->can('integration_tokens'), 403);

        if ($id > 0) {
            $this->eId = $id;
            $token = IntegrationToken::find($this->eId);
            if ($token) {
                $this->name = $token->name;
                $this->provider = $token->provider;
                $this->scopes = $token->scopes ?? ['webhook:write'];
                $this->is_active = $token->is_active;
                $this->expires_at = $token->expires_at ? $token->expires_at->format('Y-m-d\TH:i') : '';
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.integration-token.create');
    }

    public function updated($field)
    {
        $this->validateOnly($field, [
            "name" => "required|string|max:255",
            "provider" => "required|string|max:50",
            "scopes" => "nullable|array",
            "scopes.*" => "string",
            "is_active" => "nullable|boolean",
            "expires_at" => "nullable|date",
        ]);
    }

    public function addScope()
    {
        $this->scopes[] = '';
    }

    public function removeScope($index)
    {
        unset($this->scopes[$index]);
        $this->scopes = array_values($this->scopes);
    }

    public function store()
    {
        $this->validate([
            "name" => "required|string|max:255",
            "provider" => "required|string|max:50",
            "scopes" => "nullable|array",
            "scopes.*" => "string",
            "is_active" => "nullable|boolean",
            "expires_at" => "nullable|date",
        ]);

        // تحقق من عدم تكرار (provider + name)
        $exists = IntegrationToken::where('provider', $this->provider)
            ->where('name', $this->name)
            ->when($this->eId, function($query) {
                $query->where('id', '!=', $this->eId);
            })
            ->exists();

        if ($exists) {
            $this->dispatch('error', 'Token with same provider & name already exists.');
            return;
        }

        // تنظيف الـ scopes من القيم الفارغة
        $scopes = array_filter($this->scopes, function($scope) {
            return !empty(trim($scope));
        });

        if (empty($scopes)) {
            $scopes = ['webhook:write'];
        }

        $data = [
            "name" => $this->name,
            "provider" => $this->provider,
            "scopes" => array_values($scopes),
            "is_active" => $this->is_active,
            "expires_at" => $this->expires_at ? Carbon::parse($this->expires_at) : null,
        ];

        if ($this->eId > 0) {
            // تحديث التوكن الموجود
            $token = IntegrationToken::findOrFail($this->eId);
            $token->update($data);
            $this->dispatch('success', 'Token Updated Successfully!');
        } else {
            // إنشاء توكن جديد
            $rawToken = bin2hex(random_bytes(48)); // ~96 hex chars
            $tokenHash = hash('sha256', $rawToken);

            $data['token_hash'] = $tokenHash;
            $data['token_hint'] = substr($rawToken, -8);
            $data['encrypted_token'] = encrypt($rawToken); // Store encrypted token for later retrieval

            $token = IntegrationToken::create($data);

            // عرض التوكن مرة واحدة فقط
            $this->generatedToken = $rawToken;
            $this->showToken = true;

            // حفظ التوكن في الجلسة لمدة 5 دقائق للعرض لاحقاً
            session(['recently_created_token_' . $token->id => $rawToken]);

            $this->dispatch('success', 'Token Created Successfully!');
        }

        return $this->redirectRoute('admin.integration-tokens', navigate: true);
    }

    public function copyToken()
    {
        $this->dispatch('success', 'Token copied to clipboard!');
    }

    public function hideToken()
    {
        $this->showToken = false;
        $this->generatedToken = '';
    }
}
