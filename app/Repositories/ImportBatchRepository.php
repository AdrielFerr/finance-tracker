<?php

namespace App\Repositories;

use App\Imports\StatementTransaction;
use App\Models\ImportBatch;
use App\Models\ImportedTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ImportBatchRepository
{
    public function create(User $user, array $attributes): ImportBatch
    {
        return ImportBatch::create([...$attributes, 'user_id' => $user->id]);
    }

    /**
     * @param array<int, StatementTransaction> $transactions
     */
    public function storeTransactions(ImportBatch $batch, array $transactions): void
    {
        $duplicated = $this->alreadyImportedSignatures($batch->user_id, $transactions);

        foreach ($transactions as $transaction) {
            ImportedTransaction::create([
                'user_id' => $batch->user_id,
                'import_batch_id' => $batch->id,
                'external_id' => $transaction->externalId,
                'signature' => $transaction->signature(),
                'occurred_at' => $transaction->occurredAt->format('Y-m-d'),
                'description' => $transaction->description,
                'amount' => $transaction->amount,
                'direction' => $transaction->direction->value,
                'installment_number' => $transaction->installmentNumber,
                'total_installments' => $transaction->totalInstallments,
                'is_possible_duplicate' => in_array($transaction->signature(), $duplicated, true),
            ]);
        }
    }

    /**
     * @return Collection<int, ImportedTransaction>
     */
    public function transactionsOf(ImportBatch $batch): Collection
    {
        return $batch->transactions()
            ->with('category')
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return Collection<int, ImportedTransaction>
     */
    public function pendingTransactionsOf(ImportBatch $batch, array $ids): Collection
    {
        return $batch->transactions()
            ->pending()
            ->outflow()
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * @param array<int, StatementTransaction> $transactions
     * @return array<int, string>
     */
    private function alreadyImportedSignatures(int $userId, array $transactions): array
    {
        $signatures = array_map(fn (StatementTransaction $t) => $t->signature(), $transactions);

        return ImportedTransaction::forUser($userId)
            ->whereIn('signature', $signatures)
            ->where('status', 'imported')
            ->pluck('signature')
            ->all();
    }
}
