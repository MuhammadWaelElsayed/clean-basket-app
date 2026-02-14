<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIntegrationTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        // تأكد من حماية المسار بميدل وير مصادقة/صلاحيات
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required','string','max:255'],
            'provider'  => ['required','string','max:50'],
            'scopes'    => ['nullable','array'],
            'scopes.*'  => ['string'],
            'is_active' => ['nullable','boolean'],
            'expires_at'=> ['nullable','date'], // ISO8601
        ];
    }
}
