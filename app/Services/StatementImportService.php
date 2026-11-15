<?php

namespace App\Services;

use App\Imports\Exceptions\EmptyStatementException;
use App\Imports\StatementReaderResolver;
use App\Models\ImportBatch;
use App\Models\User;
use App\Repositories\ImportBatchRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class StatementImportService
{
    public function __construct(
        private StatementReaderResolver $resolver,
        private ImportBatchRepository $batches,
    ) {}

    public function importFile(User $user, UploadedFile $file, ?int $paymentMethodId): ImportBatch
    {
        $filename = $file->getClientOriginalName();
        $contents = (string) file_get_contents($file->getRealPath());

        $reader = $this->resolver->resolve($contents, $file->getClientOriginalExtension(), $filename);
        $transactions = $reader->read($contents);

        if ($transactions === []) {
            throw EmptyStatementException::forFile($filename);
        }

        return DB::transaction(function () use ($user, $filename, $paymentMethodId, $reader, $transactions) {
            $batch = $this->batches->create($user, [
                'payment_method_id' => $paymentMethodId,
                'source' => $reader->source(),
                'original_filename' => $filename,
            ]);

            $this->batches->storeTransactions($batch, $transactions);

            return $batch->fresh();
        });
    }

    public function transactionsOf(ImportBatch $batch): Collection
    {
        return $this->batches->transactionsOf($batch);
    }
}
