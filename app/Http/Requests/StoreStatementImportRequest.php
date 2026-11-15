<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStatementImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'statement' => [
                'required',
                'file',
                'max:10240',
                'extensions:ofx,csv,txt',
            ],
            'payment_method_id' => [
                'nullable',
                'integer',
                Rule::exists('payment_methods', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'statement.required' => 'Escolha o arquivo do extrato ou da fatura.',
            'statement.extensions' => 'O sistema lê arquivos OFX e CSV.',
            'statement.max' => 'O arquivo não pode ser maior que 10MB.',
            'payment_method_id.exists' => 'A forma de pagamento selecionada não existe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'statement' => 'arquivo',
            'payment_method_id' => 'forma de pagamento',
        ];
    }
}
