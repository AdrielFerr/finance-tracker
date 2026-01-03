<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:255', 'unique:tenants,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'plan' => ['required', 'in:free,basic,premium,enterprise'],
            'max_users' => ['required', 'integer', 'min:1', 'max:1000'],
            'max_expenses' => ['required', 'integer', 'min:100', 'max:1000000'],
            
            // Admin do tenant
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
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
            'admin_name' => 'nome do administrador',
            'admin_email' => 'e-mail do administrador',
            'admin_password' => 'senha do administrador',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'admin_password.confirmed' => 'A confirmação da senha não confere.',
        ];
    }
}