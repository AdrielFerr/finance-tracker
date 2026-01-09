<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id);
                }),
            ],
            'payment_method_id' => [
                'nullable',
                'integer',
                Rule::exists('payment_methods', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id);
                }),
            ],
            'description' => [
                'required',
                'string',
                'max:255',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],
            'type' => [
                'required',
                Rule::in(['fixed', 'variable', 'occasional']),
            ],
            'status' => [
                'nullable',
                Rule::in(['pending', 'paid', 'overdue', 'canceled']),
            ],
            'due_date' => [
                'required',
                'date',
            ],
            'payment_date' => [
                'nullable',
                'date',
                // ✅ REMOVIDO: 'after_or_equal:due_date'
                // Pagamento pode ser antecipado, não faz sentido restringir
            ],
            'competence_date' => [
                'nullable',
                'date',
            ],
            'is_recurring' => [
                'boolean',
            ],
            'is_installment' => [
                'boolean',
            ],
            'total_installments' => [
                'required_if:is_installment,true',
                'nullable',
                'integer',
                'min:2',
                'max:120',
            ],
            'receipt' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120', // 5MB
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'A categoria é obrigatória.',
            'category_id.exists' => 'A categoria selecionada não existe.',
            'description.required' => 'A descrição é obrigatória.',
            'description.max' => 'A descrição não pode ter mais de 255 caracteres.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'amount.max' => 'O valor não pode ser maior que R$ 999.999,99.',
            'type.required' => 'O tipo de despesa é obrigatório.',
            'type.in' => 'O tipo de despesa selecionado é inválido.',
            'due_date.required' => 'A data de vencimento é obrigatória.',
            'due_date.date' => 'A data de vencimento deve ser uma data válida.',
            'payment_date.date' => 'A data de pagamento deve ser uma data válida.',
            'total_installments.required_if' => 'O número de parcelas é obrigatório para despesas parceladas.',
            'total_installments.min' => 'O número de parcelas deve ser no mínimo 2.',
            'total_installments.max' => 'O número de parcelas não pode ser maior que 120.',
            'receipt.file' => 'O comprovante deve ser um arquivo.',
            'receipt.mimes' => 'O comprovante deve ser um arquivo PDF ou imagem (JPG, JPEG, PNG).',
            'receipt.max' => 'O comprovante não pode ser maior que 5MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'categoria',
            'payment_method_id' => 'método de pagamento',
            'description' => 'descrição',
            'notes' => 'observações',
            'amount' => 'valor',
            'type' => 'tipo',
            'status' => 'status',
            'due_date' => 'data de vencimento',
            'payment_date' => 'data de pagamento',
            'competence_date' => 'data de competência',
            'total_installments' => 'número de parcelas',
            'receipt' => 'comprovante',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Converter valor formatado (1.234,56) para float (1234.56)
        if ($this->has('amount')) {
            $amount = $this->input('amount');
            
            // Se vier com formato brasileiro
            if (is_string($amount) && strpos($amount, ',') !== false) {
                $amount = str_replace('.', '', $amount); // Remove separador de milhar
                $amount = str_replace(',', '.', $amount); // Substitui vírgula por ponto
            }
            
            $this->merge(['amount' => $amount]);
        }

        // Definir competence_date baseado em due_date se não fornecido
        if (!$this->has('competence_date') && $this->has('due_date')) {
            $this->merge([
                'competence_date' => date('Y-m-01', strtotime($this->input('due_date')))
            ]);
        }

        // ✅ ADICIONAR: Limpar payment_date se status não for 'paid'
        if ($this->input('status') !== 'paid') {
            $this->merge(['payment_date' => null]);
        }

        // ✅ ADICIONAR: Definir payment_date se status for 'paid' mas não foi preenchido
        if ($this->input('status') === 'paid' && empty($this->input('payment_date'))) {
            $this->merge(['payment_date' => now()->format('Y-m-d')]);
        }

        // Converter checkboxes para boolean
        $this->merge([
            'is_recurring' => $this->boolean('is_recurring'),
            'is_installment' => $this->boolean('is_installment'),
        ]);
    }
}