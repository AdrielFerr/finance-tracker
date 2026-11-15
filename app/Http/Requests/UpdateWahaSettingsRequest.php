<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWahaSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'super_admin';
    }

    public function rules(): array
    {
        return [
            'waha_url' => ['nullable', 'url', 'max:255'],
            'waha_session' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/'],
            'waha_api_key' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'waha_url.url' => 'Informe um endereço completo, como https://apiwaha.exemplo.com.br',
            'waha_session.regex' => 'O nome da sessão aceita apenas letras, números, hífen e underline.',
        ];
    }

    public function attributes(): array
    {
        return [
            'waha_url' => 'endereço do servidor',
            'waha_session' => 'sessão',
            'waha_api_key' => 'chave de acesso',
        ];
    }
}
