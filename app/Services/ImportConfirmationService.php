<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ImportBatch;
use App\Models\ImportedTransaction;
use App\Repositories\ImportBatchRepository;
use Illuminate\Support\Facades\DB;

class ImportConfirmationService
{
    private const IMPORTED_EXPENSE_TYPE = 'variable';
    private const IMPORTED_EXPENSE_STATUS = 'paid';

    public function __construct(
        private ImportBatchRepository $batches,
        private ExpenseService $expenses,
    ) {}

    /**
     * @param array<int, int> $categoryByTransactionId
     */
    public function confirm(ImportBatch $batch, array $categoryByTransactionId): int
    {
        return DB::transaction(function () use ($batch, $categoryByTransactionId) {
            $transactions = $this->batches->pendingTransactionsOf($batch, array_keys($categoryByTransactionId));

            foreach ($transactions as $transaction) {
                $this->convertToExpense($batch, $transaction, $categoryByTransactionId[$transaction->id]);
            }

            $batch->update(['status' => 'confirmed']);

            return $transactions->count();
        });
    }

    public function discard(ImportBatch $batch): void
    {
        DB::transaction(function () use ($batch) {
            $batch->transactions()->pending()->update(['status' => 'ignored']);
            $batch->update(['status' => 'discarded']);
        });
    }

    private function convertToExpense(ImportBatch $batch, ImportedTransaction $transaction, int $categoryId): Expense
    {
        $expense = $this->expenses->createExpense(
            $batch->user,
            $this->expenseDataFrom($batch, $transaction, $categoryId)
        );

        $transaction->update([
            'status' => 'imported',
            'category_id' => $categoryId,
            'expense_id' => $expense->id,
        ]);

        return $expense;
    }

    private function expenseDataFrom(ImportBatch $batch, ImportedTransaction $transaction, int $categoryId): array
    {
        $occurredAt = $transaction->occurred_at;

        return [
            'category_id' => $categoryId,
            'payment_method_id' => $batch->payment_method_id,
            'description' => $transaction->description,
            'amount' => $transaction->amount,
            'type' => self::IMPORTED_EXPENSE_TYPE,
            'status' => self::IMPORTED_EXPENSE_STATUS,
            'due_date' => $occurredAt->format('Y-m-d'),
            'payment_date' => $occurredAt->format('Y-m-d'),
            'competence_date' => $occurredAt->copy()->startOfMonth()->format('Y-m-d'),
            'is_installment' => $transaction->isInstallment(),
            'installment_number' => $transaction->installment_number,
            'total_installments' => $transaction->total_installments,
        ];
    }
}
