<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ExpenseRepository
{
    /**
     * Busca despesas com filtros e paginação
     */
    public function findByUser(
        User $user, 
        array $filters = [], 
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Expense::query()
            ->with(['category', 'paymentMethod'])
            ->where('user_id', $user->id);

        // Filtro por categoria
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filtro por método de pagamento
        if (!empty($filters['payment_method_id'])) {
            $query->where('payment_method_id', $filters['payment_method_id']);
        }

        // Filtro por status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filtro por tipo
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filtro por data de vencimento específica
        if (!empty($filters['due_date'])) {
            $query->whereDate('due_date', $filters['due_date']);
        }

        // Filtro por período de competência
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('competence_date', [
                $filters['start_date'],
                $filters['end_date']
            ]);
        }

        // Filtro por mês/ano
        if (!empty($filters['month']) && !empty($filters['year'])) {
            $startDate = Carbon::create($filters['year'], $filters['month'], 1)->startOfMonth();
            $endDate = Carbon::create($filters['year'], $filters['month'], 1)->endOfMonth();
            $query->whereBetween('competence_date', [$startDate, $endDate]);
        }

        // Busca por descrição
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('description', 'like', "%{$filters['search']}%")
                  ->orWhere('notes', 'like', "%{$filters['search']}%");
            });
        }

        // Ordenação
        $sortBy = $filters['sort_by'] ?? 'due_date';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Busca despesas de um mês específico
     */
    public function getMonthlyExpenses(User $user, int $year, int $month): Collection
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        return Expense::with(['category', 'paymentMethod'])
            ->where('user_id', $user->id)
            ->whereBetween('competence_date', [$startDate, $endDate])
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Calcula o total de despesas em um período
     */
    public function getTotalByPeriod(
        User $user, 
        Carbon $startDate, 
        Carbon $endDate,
        ?string $status = null
    ): float {
        $query = Expense::where('user_id', $user->id)
            ->whereBetween('competence_date', [$startDate, $endDate]);

        if ($status) {
            $query->where('status', $status);
        } else {
            // Não incluir despesas canceladas por padrão
            $query->where('status', '!=', 'canceled');
        }

        return (float) $query->sum('amount');
    }

    /**
     * Busca despesas vencidas
     */
    public function getOverdueExpenses(User $user): Collection
    {
        return Expense::with(['category', 'paymentMethod'])
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('due_date', '<', Carbon::today())
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Busca despesas que vencem em breve
     */
    public function getUpcomingExpenses(User $user, int $days = 7): Collection
    {
        return Expense::with(['category', 'paymentMethod'])
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [
                Carbon::today(),
                Carbon::today()->addDays($days)
            ])
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Busca gastos por categoria em um período
     */
    public function getTotalByCategory(
        User $user, 
        Carbon $startDate, 
        Carbon $endDate
    ): Collection {
        return Expense::selectRaw('category_id, SUM(amount) as total')
            ->with('category')
            ->where('user_id', $user->id)
            ->whereBetween('competence_date', [$startDate, $endDate])
            ->where('status', '!=', 'canceled')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Busca gastos por método de pagamento
     */
    public function getTotalByPaymentMethod(
        User $user, 
        Carbon $startDate, 
        Carbon $endDate
    ): Collection {
        return Expense::selectRaw('payment_method_id, SUM(amount) as total')
            ->with('paymentMethod')
            ->where('user_id', $user->id)
            ->whereBetween('competence_date', [$startDate, $endDate])
            ->where('status', '!=', 'canceled')
            ->whereNotNull('payment_method_id')
            ->groupBy('payment_method_id')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Cria uma nova despesa
     */
    public function create(array $data): Expense
    {
        return Expense::create($data);
    }

    /**
     * Atualiza uma despesa
     */
    public function update(Expense $expense, array $data): bool
    {
        return $expense->update($data);
    }

    /**
     * Deleta uma despesa
     */
    public function delete(Expense $expense): bool
    {
        return $expense->delete();
    }

    /**
     * Calcula estatísticas comparativas entre dois períodos
     */
    public function getComparativeStats(
        User $user,
        Carbon $currentStart,
        Carbon $currentEnd,
        Carbon $previousStart,
        Carbon $previousEnd
    ): array {
        $currentTotal = $this->getTotalByPeriod($user, $currentStart, $currentEnd);
        $previousTotal = $this->getTotalByPeriod($user, $previousStart, $previousEnd);

        $difference = $currentTotal - $previousTotal;
        $percentageChange = $previousTotal > 0 
            ? (($difference / $previousTotal) * 100) 
            : 0;

        return [
            'current_total' => $currentTotal,
            'previous_total' => $previousTotal,
            'difference' => $difference,
            'percentage_change' => round($percentageChange, 2),
            'is_increase' => $difference > 0,
        ];
    }

    /**
     * Retorna as maiores despesas do período
     */
    public function getTopExpenses(
        User $user, 
        Carbon $startDate, 
        Carbon $endDate,
        int $limit = 10
    ): Collection {
        return Expense::with(['category', 'paymentMethod'])
            ->where('user_id', $user->id)
            ->whereBetween('competence_date', [$startDate, $endDate])
            ->where('status', '!=', 'canceled')
            ->orderByDesc('amount')
            ->limit($limit)
            ->get();
    }

    /**
     * Calcula a média de gastos por dia no período
     */
    public function getDailyAverage(
        User $user, 
        Carbon $startDate, 
        Carbon $endDate
    ): float {
        $total = $this->getTotalByPeriod($user, $startDate, $endDate);
        $days = $startDate->diffInDays($endDate) + 1;

        return $days > 0 ? $total / $days : 0;
    }
}