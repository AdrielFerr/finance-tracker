<?php

namespace App\Http\Requests;

use App\Services\WhatsApp\BrazilianPhone;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'notify_days_before' => ['required', 'integer', 'min:1', 'max:60'],
            'notify_on_due_date' => ['boolean'],
            'notify_when_overdue' => ['boolean'],
            'notify_in_app' => ['boolean'],
            'notify_by_email' => ['boolean'],
            'notify_by_whatsapp' => ['boolean'],
            'whatsapp_number' => [
                'nullable',
                'required_if:notify_by_whatsapp,true',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    if (filled($value) && !app(BrazilianPhone::class)->isValid($value)) {
                        $fail('Informe um número com DDD, como (11) 99999-9999.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'notify_days_before.required' => 'Informe com quantos dias de antecedência você quer ser avisado.',
            'notify_days_before.min' => 'A antecedência deve ser de pelo menos 1 dia.',
            'notify_days_before.max' => 'A antecedência não pode passar de 60 dias.',
            'whatsapp_number.required_if' => 'Informe o número para receber avisos no WhatsApp.',
        ];
    }

    public function attributes(): array
    {
        return [
            'notify_days_before' => 'antecedência',
            'whatsapp_number' => 'número de WhatsApp',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'notify_on_due_date' => $this->boolean('notify_on_due_date'),
            'notify_when_overdue' => $this->boolean('notify_when_overdue'),
            'notify_in_app' => $this->boolean('notify_in_app'),
            'notify_by_email' => $this->boolean('notify_by_email'),
            'notify_by_whatsapp' => $this->boolean('notify_by_whatsapp'),
        ]);
    }
}
