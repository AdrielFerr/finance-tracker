<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmStatementImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['integer'],
            'categories' => ['array'],
            'categories.*' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id);
                }),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->selectedIds() as $id) {
                if (empty($this->input("categories.{$id}"))) {
                    $validator->errors()->add("categories.{$id}", 'Escolha a categoria deste lançamento.');
                }
            }
        });
    }

    /**
     * @return array<int, int>
     */
    public function categoryByTransactionId(): array
    {
        $categories = [];

        foreach ($this->selectedIds() as $id) {
            $categories[$id] = (int) $this->input("categories.{$id}");
        }

        return $categories;
    }

    /**
     * @return array<int, int>
     */
    private function selectedIds(): array
    {
        return array_map('intval', $this->input('selected', []));
    }

    public function messages(): array
    {
        return [
            'selected.required' => 'Selecione pelo menos um lançamento para importar.',
            'selected.min' => 'Selecione pelo menos um lançamento para importar.',
        ];
    }
}
