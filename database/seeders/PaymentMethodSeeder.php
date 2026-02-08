<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        $defaultPaymentMethods = [
            [
                'name' => 'Nubank',
                'type' => 'credit_card',
                'last_four_digits' => '1234',
                'due_day' => 10,
                'closing_day' => 1,
                'credit_limit' => 5000.00,
                'brand' => 'Mastercard',
                'color' => '#8A05BE',
            ],
            [
                'name' => 'Inter',
                'type' => 'credit_card',
                'last_four_digits' => '5678',
                'due_day' => 15,
                'closing_day' => 5,
                'credit_limit' => 3000.00,
                'brand' => 'Visa',
                'color' => '#FF7A00',
            ],
            [
                'name' => 'Débito Itaú',
                'type' => 'debit_card',
                'last_four_digits' => '9012',
                'brand' => 'Visa',
                'color' => '#0050A0',
            ],
            [
                'name' => 'PIX',
                'type' => 'pix',
                'color' => '#32BCAD',
            ],
            [
                'name' => 'Dinheiro',
                'type' => 'cash',
                'color' => '#059669',
            ],
            [
                'name' => 'Boleto',
                'type' => 'bank_slip',
                'color' => '#F59E0B',
            ],
        ];

        foreach ($users as $user) {
            foreach ($defaultPaymentMethods as $method) {
                PaymentMethod::create(array_merge($method, [
                    'user_id' => $user->id,
                    'is_active' => true,
                ]));
            }
        }
    }
}