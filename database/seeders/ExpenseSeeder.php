<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        
        if (!$user) {
            return;
        }

        $categories = Category::where('user_id', $user->id)->get();
        $paymentMethods = PaymentMethod::where('user_id', $user->id)->get();

        if ($categories->isEmpty() || $paymentMethods->isEmpty()) {
            return;
        }

        // Despesas do mês atual
        $this->createExpensesForMonth($user, $categories, $paymentMethods, Carbon::now());
        
        // Despesas do mês passado
        $this->createExpensesForMonth($user, $categories, $paymentMethods, Carbon::now()->subMonth());
        
        // Despesas de 2 meses atrás
        $this->createExpensesForMonth($user, $categories, $paymentMethods, Carbon::now()->subMonths(2));
    }

    private function createExpensesForMonth($user, $categories, $paymentMethods, Carbon $date)
    {
        $expenses = [
            // Alimentação
            [
                'category' => 'Alimentação',
                'description' => 'Supermercado Extra',
                'amount' => rand(250, 450),
                'type' => 'variable',
                'status' => 'paid',
                'day' => rand(1, 10),
            ],
            [
                'category' => 'Alimentação',
                'description' => 'iFood',
                'amount' => rand(50, 120),
                'type' => 'variable',
                'status' => 'paid',
                'day' => rand(5, 15),
            ],
            [
                'category' => 'Alimentação',
                'description' => 'Restaurante',
                'amount' => rand(80, 150),
                'type' => 'occasional',
                'status' => $date->isFuture() ? 'pending' : 'paid',
                'day' => rand(15, 25),
            ],
            
            // Moradia
            [
                'category' => 'Moradia',
                'description' => 'Aluguel',
                'amount' => 1200.00,
                'type' => 'fixed',
                'status' => $date->isFuture() ? 'pending' : 'paid',
                'day' => 10,
            ],
            [
                'category' => 'Moradia',
                'description' => 'Condomínio',
                'amount' => 350.00,
                'type' => 'fixed',
                'status' => $date->isFuture() ? 'pending' : 'paid',
                'day' => 15,
            ],
            
            // Contas
            [
                'category' => 'Contas',
                'description' => 'Energia Elétrica (Energisa)',
                'amount' => rand(120, 200),
                'type' => 'variable',
                'status' => $date->isFuture() ? 'pending' : 'paid',
                'day' => 5,
            ],
            [
                'category' => 'Contas',
                'description' => 'Água (Cagepa)',
                'amount' => rand(60, 100),
                'type' => 'variable',
                'status' => $date->isFuture() ? 'pending' : 'paid',
                'day' => 8,
            ],
            [
                'category' => 'Contas',
                'description' => 'Internet (Vivo Fibra)',
                'amount' => 99.90,
                'type' => 'fixed',
                'status' => $date->isFuture() ? 'pending' : 'paid',
                'day' => 12,
            ],
            [
                'category' => 'Contas',
                'description' => 'Celular (Claro)',
                'amount' => 79.90,
                'type' => 'fixed',
                'status' => $date->isFuture() ? 'pending' : 'paid',
                'day' => 20,
            ],
            
            // Transporte
            [
                'category' => 'Transporte',
                'description' => 'Combustível',
                'amount' => rand(200, 350),
                'type' => 'variable',
                'status' => 'paid',
                'day' => rand(5, 20),
            ],
            [
                'category' => 'Transporte',
                'description' => 'Uber',
                'amount' => rand(40, 80),
                'type' => 'variable',
                'status' => 'paid',
                'day' => rand(10, 25),
            ],
            
            // Lazer
            [
                'category' => 'Lazer',
                'description' => 'Netflix',
                'amount' => 55.90,
                'type' => 'fixed',
                'status' => $date->isFuture() ? 'pending' : 'paid',
                'day' => 1,
            ],
            [
                'category' => 'Lazer',
                'description' => 'Spotify',
                'amount' => 21.90,
                'type' => 'fixed',
                'status' => $date->isFuture() ? 'pending' : 'paid',
                'day' => 3,
            ],
            [
                'category' => 'Lazer',
                'description' => 'Cinema',
                'amount' => rand(60, 100),
                'type' => 'occasional',
                'status' => 'paid',
                'day' => rand(15, 28),
            ],
            
            // Saúde
            [
                'category' => 'Saúde',
                'description' => 'Plano de Saúde (Hapvida)',
                'amount' => 350.00,
                'type' => 'fixed',
                'status' => $date->isFuture() ? 'pending' : 'paid',
                'day' => 5,
            ],
            [
                'category' => 'Saúde',
                'description' => 'Farmácia',
                'amount' => rand(50, 150),
                'type' => 'variable',
                'status' => 'paid',
                'day' => rand(10, 20),
            ],
        ];

        foreach ($expenses as $expenseData) {
            $category = $categories->where('name', $expenseData['category'])->first();
            
            if (!$category) {
                continue;
            }

            $dueDate = $date->copy()->day($expenseData['day']);
            $paymentDate = $expenseData['status'] === 'paid' ? $dueDate->copy() : null;

            Expense::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'payment_method_id' => $paymentMethods->random()->id,
                'description' => $expenseData['description'],
                'amount' => $expenseData['amount'],
                'type' => $expenseData['type'],
                'status' => $expenseData['status'],
                'due_date' => $dueDate,
                'payment_date' => $paymentDate,
                'competence_date' => $date->copy()->startOfMonth(),
                'is_recurring' => false,
            ]);
        }
    }
}