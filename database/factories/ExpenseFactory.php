<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $dueDate = now()->addDays(3);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'description' => fake()->words(3, true),
            'amount' => fake()->randomFloat(2, 10, 2000),
            'type' => 'variable',
            'status' => 'pending',
            'due_date' => $dueDate,
            'competence_date' => $dueDate->copy()->startOfMonth(),
        ];
    }

    public function dueIn(int $days): static
    {
        return $this->state(fn () => [
            'due_date' => now()->addDays($days),
            'competence_date' => now()->addDays($days)->startOfMonth(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
            'payment_date' => now(),
        ]);
    }
}
