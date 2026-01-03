<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\RecurringExpense;
use App\Models\User;
use App\Repositories\ExpenseRepository;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function __construct(
        private ExpenseRepository $expenseRepository
    ) {}

    /**
     * Cria uma nova despesa
     */
    public function createExpense(User $user, array $data): Expense
    {
        DB::beginTransaction();

        try {
            // Processar upload de comprovante se existir
            if (isset($data['receipt']) && $data['receipt'] instanceof UploadedFile) {
                $data['receipt_path'] = $this->storeReceipt($data['receipt'], $user->id);
                unset($data['receipt']);
            }

            // Definir competência se não fornecida
            if (empty($data['competence_date'])) {
                $data['competence_date'] = Carbon::parse($data['due_date'])->format('Y-m-01');
            }

            // Adicionar user_id
            $data['user_id'] = $user->id;

            // Criar despesa
            $expense = $this->expenseRepository->create($data);

            DB::commit();

            return $expense;
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Remover arquivo se houve erro
            if (isset($data['receipt_path'])) {
                Storage::delete($data['receipt_path']);
            }

            throw $e;
        }
    }

    /**
     * Atualiza uma despesa
     */
    public function updateExpense(Expense $expense, array $data): Expense
    {
        DB::beginTransaction();

        try {
            // Processar novo comprovante
            if (isset($data['receipt']) && $data['receipt'] instanceof UploadedFile) {
                // Deletar comprovante antigo
                if ($expense->receipt_path) {
                    Storage::delete($expense->receipt_path);
                }

                $data['receipt_path'] = $this->storeReceipt($data['receipt'], $expense->user_id);
                unset($data['receipt']);
            }

            $this->expenseRepository->update($expense, $data);

            DB::commit();

            return $expense->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Marca despesa como paga
     */
    public function markAsPaid(Expense $expense, ?string $paymentDate = null): Expense
    {
        $expense->markAsPaid($paymentDate);
        return $expense;
    }

    /**
     * Cria despesa recorrente e suas instâncias
     */
    public function createRecurringExpense(User $user, array $data, int $monthsToGenerate = 12): RecurringExpense
    {
        DB::beginTransaction();

        try {
            $data['user_id'] = $user->id;
            
            $recurringExpense = RecurringExpense::create($data);

            // Gerar despesas futuras
            if ($data['auto_generate'] ?? true) {
                $this->generateExpensesFromRecurring($recurringExpense, $monthsToGenerate);
            }

            DB::commit();

            return $recurringExpense;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Gera despesas a partir de uma despesa recorrente
     */
    public function generateExpensesFromRecurring(
        RecurringExpense $recurring, 
        int $monthsToGenerate = 12
    ): int {
        $generated = 0;
        $startDate = Carbon::parse($recurring->start_date);
        $currentDate = Carbon::now();

        // Determinar a partir de quando gerar
        $generateFrom = $startDate->greaterThan($currentDate) 
            ? $startDate 
            : $currentDate;

        for ($i = 0; $i < $monthsToGenerate; $i++) {
            $dueDate = $this->calculateNextDueDate($generateFrom, $recurring->frequency, $i);

            // Parar se passou da data final
            if ($recurring->end_date && $dueDate->greaterThan($recurring->end_date)) {
                break;
            }

            // Verificar se já existe despesa para esta competência
            $competenceDate = $dueDate->copy()->startOfMonth();
            $exists = Expense::where('recurring_expense_id', $recurring->id)
                ->where('competence_date', $competenceDate)
                ->exists();

            if (!$exists) {
                Expense::create([
                    'user_id' => $recurring->user_id,
                    'category_id' => $recurring->category_id,
                    'payment_method_id' => $recurring->payment_method_id,
                    'description' => $recurring->description,
                    'notes' => $recurring->notes,
                    'amount' => $recurring->amount,
                    'type' => $recurring->type,
                    'status' => 'pending',
                    'due_date' => $dueDate,
                    'competence_date' => $competenceDate,
                    'is_recurring' => true,
                    'recurring_expense_id' => $recurring->id,
                ]);

                $generated++;
            }
        }

        return $generated;
    }

    /**
     * Cria despesas parceladas
     */
    public function createInstallmentExpenses(User $user, array $data): array
    {
        DB::beginTransaction();

        try {
            $expenses = [];
            $totalInstallments = $data['total_installments'];
            $installmentAmount = $data['amount'] / $totalInstallments;
            $firstDueDate = Carbon::parse($data['due_date']);

            for ($i = 1; $i <= $totalInstallments; $i++) {
                $dueDate = $firstDueDate->copy()->addMonths($i - 1);
                $competenceDate = $dueDate->copy()->startOfMonth();

                $expenseData = array_merge($data, [
                    'user_id' => $user->id,
                    'amount' => $installmentAmount,
                    'due_date' => $dueDate,
                    'competence_date' => $competenceDate,
                    'is_installment' => true,
                    'installment_number' => $i,
                    'total_installments' => $totalInstallments,
                ]);

                unset($expenseData['receipt']); // Não duplicar comprovante

                $expenses[] = $this->expenseRepository->create($expenseData);
            }

            DB::commit();

            return $expenses;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Deleta uma despesa
     */
    public function deleteExpense(Expense $expense): bool
    {
        return $this->expenseRepository->delete($expense);
    }

    /**
     * Armazena comprovante
     */
    private function storeReceipt(UploadedFile $file, int $userId): string
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        
        return $file->store("receipts/{$userId}/{$year}/{$month}", 'public');
    }

    /**
     * Calcula próxima data de vencimento baseada na frequência
     */
    private function calculateNextDueDate(Carbon $baseDate, string $frequency, int $iteration): Carbon
    {
        return match($frequency) {
            'monthly' => $baseDate->copy()->addMonths($iteration),
            'bimonthly' => $baseDate->copy()->addMonths($iteration * 2),
            'quarterly' => $baseDate->copy()->addMonths($iteration * 3),
            'semiannual' => $baseDate->copy()->addMonths($iteration * 6),
            'annual' => $baseDate->copy()->addYears($iteration),
            default => $baseDate->copy()->addMonths($iteration),
        };
    }

    /**
     * Atualiza status de despesas vencidas
     */
    public function updateOverdueExpenses(User $user): int
    {
        return Expense::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('due_date', '<', Carbon::today())
            ->update(['status' => 'overdue']);
    }

    /**
     * Duplica uma despesa para outro mês
     */
    public function duplicateExpense(Expense $expense, Carbon $newDueDate): Expense
    {
        $newExpense = $expense->replicate();
        $newExpense->due_date = $newDueDate;
        $newExpense->competence_date = $newDueDate->copy()->startOfMonth();
        $newExpense->status = 'pending';
        $newExpense->payment_date = null;
        $newExpense->receipt_path = null; // Não duplicar comprovante
        $newExpense->save();

        return $newExpense;
    }
}