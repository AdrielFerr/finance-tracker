<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role === 'super_admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('tenants', 'email')->ignore($this->route('tenant'))
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'plan' => ['required', 'in:free,basic,premium,enterprise'],
            'max_users' => ['required', 'integer', 'min:1', 'max:1000'],
            'max_expenses' => ['required', 'integer', 'min:100', 'max:1000000'],
            'status' => ['required', 'in:active,suspended,cancelled'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome da empresa',
            'email' => 'e-mail',
            'phone' => 'telefone',
            'address' => 'endereço',
            'plan' => 'plano',
            'max_users' => 'máximo de usuários',
            'max_expenses' => 'máximo de despesas',
            'status' => 'status',
        ];
    }
}