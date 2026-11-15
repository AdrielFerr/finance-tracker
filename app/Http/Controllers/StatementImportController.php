<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmStatementImportRequest;
use App\Http\Requests\StoreStatementImportRequest;
use App\Imports\Exceptions\EmptyStatementException;
use App\Imports\Exceptions\UnsupportedStatementException;
use App\Models\Category;
use App\Models\ImportBatch;
use App\Services\ImportConfirmationService;
use App\Services\StatementImportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class StatementImportController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private StatementImportService $imports,
        private ImportConfirmationService $confirmations,
    ) {}

    public function store(StoreStatementImportRequest $request)
    {
        try {
            $batch = $this->imports->importFile(
                $request->user(),
                $request->file('statement'),
                $request->input('payment_method_id') ? (int) $request->input('payment_method_id') : null
            );

            return redirect()->route('expenses.import.show', $batch);
        } catch (UnsupportedStatementException|EmptyStatementException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(ImportBatch $import)
    {
        $this->authorize('view', $import);

        return view('expenses.import', [
            'batch' => $import,
            'transactions' => $this->imports->transactionsOf($import),
            'categories' => Category::forUser(Auth::id())->active()->orderBy('name')->get(),
        ]);
    }

    public function confirm(ConfirmStatementImportRequest $request, ImportBatch $import)
    {
        $this->authorize('update', $import);

        $imported = $this->confirmations->confirm($import, $request->categoryByTransactionId());

        return redirect()
            ->route('expenses.index')
            ->with('success', "{$imported} despesa(s) importada(s) com sucesso!");
    }

    public function destroy(ImportBatch $import)
    {
        $this->authorize('delete', $import);

        $this->confirmations->discard($import);

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Importação descartada. Nenhuma despesa foi criada.');
    }
}
