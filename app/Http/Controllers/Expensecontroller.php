<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Http\Requests\StoreExpenseRequest;
use App\Services\ExpenseService;
use App\Repositories\ExpenseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;


class ExpenseController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct(
        private ExpenseService $expenseService,
        private ExpenseRepository $expenseRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Filtros
        $filters = [
            'category_id' => $request->input('category_id'),
            'payment_method_id' => $request->input('payment_method_id'),
            'status' => $request->input('status'),
            'type' => $request->input('type'),
            'due_date' => $request->input('due_date'), // Filtro por data de vencimento
            'month' => $request->input('month', now()->month),
            'year' => $request->input('year', now()->year),
            'search' => $request->input('search'),
            'sort_by' => $request->input('sort_by', 'due_date'),
            'sort_order' => $request->input('sort_order', 'desc'),
        ];

        // Se tiver filtro de data específica, não usar filtro de mês/ano
        if ($filters['due_date']) {
            unset($filters['month'], $filters['year']);
        }

        $expenses = $this->expenseRepository->findByUser($user, $filters, 15);

        // Dados para os filtros
        $categories = Category::forUser($user->id)->active()->orderBy('name')->get();
        $paymentMethods = PaymentMethod::forUser($user->id)->active()->orderBy('name')->get();

        // Estatísticas
        $totalAmount = 0;
        if ($filters['due_date']) {
            // Total das despesas da data específica
            $totalAmount = $expenses->sum('amount');
        } elseif (isset($filters['month']) && isset($filters['year'])) {
            $totalAmount = $this->expenseRepository->getTotalByPeriod(
                $user,
                now()->create($filters['year'], $filters['month'], 1)->startOfMonth(),
                now()->create($filters['year'], $filters['month'], 1)->endOfMonth()
            );
        }

        $stats = [
            'total' => $expenses->total(),
            'total_amount' => $totalAmount,
        ];

        return view('expenses.index', compact('expenses', 'categories', 'paymentMethods', 'filters', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        $categories = Category::forUser($user->id)->active()->orderBy('name')->get();
        $paymentMethods = PaymentMethod::forUser($user->id)->active()->orderBy('name')->get();

        return view('expenses.create', compact('categories', 'paymentMethods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        try {
            $user = Auth::user();
            $data = $request->validated();

            // Verifica se é parcelamento
            if ($request->boolean('is_installment') && $request->input('total_installments') > 1) {
                $expenses = $this->expenseService->createInstallmentExpenses($user, $data);
                
                return redirect()
                    ->route('expenses.index')
                    ->with('success', count($expenses) . ' despesas parceladas criadas com sucesso!');
            }

            // Despesa simples
            $expense = $this->expenseService->createExpense($user, $data);

            return redirect()
                ->route('expenses.index')
                ->with('success', 'Despesa criada com sucesso!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao criar despesa: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        // Verificar propriedade
        $this->authorize('view', $expense);

        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        // Verificar propriedade
        $this->authorize('update', $expense);

        $user = Auth::user();
        
        $categories = Category::forUser($user->id)->active()->orderBy('name')->get();
        $paymentMethods = PaymentMethod::forUser($user->id)->active()->orderBy('name')->get();

        return view('expenses.edit', compact('expense', 'categories', 'paymentMethods'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreExpenseRequest $request, Expense $expense)
    {
        // Verificar propriedade
        $this->authorize('update', $expense);

        try {
            $data = $request->validated();
            $this->expenseService->updateExpense($expense, $data);

            return redirect()
                ->route('expenses.index')
                ->with('success', 'Despesa atualizada com sucesso!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar despesa: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        // Verificar propriedade
        $this->authorize('delete', $expense);

        try {
            $this->expenseService->deleteExpense($expense);

            return redirect()
                ->route('expenses.index')
                ->with('success', 'Despesa excluída com sucesso!');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Erro ao excluir despesa: ' . $e->getMessage());
        }
    }

    /**
     * Marca despesa como paga
     */
    public function markAsPaid(Request $request, Expense $expense)
    {
        // Verificar propriedade
        $this->authorize('update', $expense);

        try {
            $paymentDate = $request->input('payment_date', now()->format('Y-m-d'));
            $this->expenseService->markAsPaid($expense, $paymentDate);

            return back()->with('success', 'Despesa marcada como paga!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao marcar despesa como paga: ' . $e->getMessage());
        }
    }

    /**
     * Duplica uma despesa para outro mês
     */
    public function duplicate(Request $request, Expense $expense)
    {
        // Verificar propriedade
        $this->authorize('create', Expense::class);

        try {
            $newDueDate = $request->input('due_date') 
                ? \Carbon\Carbon::parse($request->input('due_date'))
                : now()->addMonth();

            $newExpense = $this->expenseService->duplicateExpense($expense, $newDueDate);

            return redirect()
                ->route('expenses.index')
                ->with('success', 'Despesa duplicada com sucesso!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao duplicar despesa: ' . $e->getMessage());
        }
    }

    /**
     * Download do comprovante
     */
    public function downloadReceipt(Expense $expense)
    {
        $this->authorize('view', $expense);

        // dd("teste");

        if (!$expense->receipt_path) {
            return back()->with('error', 'Despesa sem comprovante.');
        }

        if (!Storage::disk('public')->exists($expense->receipt_path)) {
            return back()->with('error', 'Arquivo não encontrado.');
        }

        // Caminho completo
        $fullPath = Storage::disk('public')->path($expense->receipt_path);

        // Nome amigável
        $extension = pathinfo($expense->receipt_path, PATHINFO_EXTENSION);
        $fileName = 'comprovante-' . $expense->id . '-' . now()->format('Y-m-d') . '.' . $extension;

        // SEM AVISO DO INTELLISENSE
        return response()->download($fullPath, $fileName);
    }

    /**
     * Exclusão em massa de despesas
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = explode(',', $request->input('ids'));
            $user = Auth::user();
            
            // Buscar apenas despesas do usuário
            $expenses = Expense::whereIn('id', $ids)
                ->where('user_id', $user->id)
                ->get();

            if ($expenses->isEmpty()) {
                return back()->with('error', 'Nenhuma despesa encontrada para excluir.');
            }

            $count = $expenses->count();

            // Excluir cada despesa
            foreach ($expenses as $expense) {
                $this->expenseService->deleteExpense($expense);
            }

            return redirect()
                ->route('expenses.index')
                ->with('success', "{$count} despesa(s) excluída(s) com sucesso!");

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao excluir despesas: ' . $e->getMessage());
        }
    }
}