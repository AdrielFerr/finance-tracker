<?php

namespace App\Http\Controllers;

use App\Repositories\ExpenseRepository;
use App\Services\ExpenseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private ExpenseRepository $expenseRepository,
        private ExpenseService $expenseService
    ) {}

    /**
     * Exibe o dashboard principal
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Definir período (mês atual por padrão)
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        $currentStart = Carbon::create($year, $month, 1)->startOfMonth();
        $currentEnd = Carbon::create($year, $month, 1)->endOfMonth();
        
        // Período anterior para comparação
        $previousStart = $currentStart->copy()->subMonth()->startOfMonth();
        $previousEnd = $currentStart->copy()->subMonth()->endOfMonth();

        // Buscar dados
        $currentExpenses = $this->expenseRepository->getMonthlyExpenses($user, $year, $month);
        
        // Estatísticas do mês atual
        $currentStats = [
            'total' => $this->expenseRepository->getTotalByPeriod($user, $currentStart, $currentEnd),
            'paid' => $this->expenseRepository->getTotalByPeriod($user, $currentStart, $currentEnd, 'paid'),
            'pending' => $this->expenseRepository->getTotalByPeriod($user, $currentStart, $currentEnd, 'pending'),
            'overdue' => $this->expenseRepository->getTotalByPeriod($user, $currentStart, $currentEnd, 'overdue'),
        ];

        // Comparativo com mês anterior
        $comparison = $this->expenseRepository->getComparativeStats(
            $user,
            $currentStart,
            $currentEnd,
            $previousStart,
            $previousEnd
        );

        // Gastos por categoria
        $expensesByCategory = $this->expenseRepository->getTotalByCategory(
            $user,
            $currentStart,
            $currentEnd
        );

        // Gastos por método de pagamento
        $expensesByPaymentMethod = $this->expenseRepository->getTotalByPaymentMethod(
            $user,
            $currentStart,
            $currentEnd
        );

        // Despesas vencidas
        $overdueExpenses = $this->expenseRepository->getOverdueExpenses($user);

        // Próximas despesas (7 dias)
        $upcomingExpenses = $this->expenseRepository->getUpcomingExpenses($user, 7);

        // Maiores despesas do mês
        $topExpenses = $this->expenseRepository->getTopExpenses(
            $user,
            $currentStart,
            $currentEnd,
            5
        );

        // Média diária
        $dailyAverage = $this->expenseRepository->getDailyAverage(
            $user,
            $currentStart,
            $currentEnd
        );

        // Dados para gráficos
        $chartData = $this->prepareChartData($user, $year, $month);

        return view('dashboard.index', compact(
            'currentStats',
            'comparison',
            'expensesByCategory',
            'expensesByPaymentMethod',
            'overdueExpenses',
            'upcomingExpenses',
            'topExpenses',
            'dailyAverage',
            'chartData',
            'year',
            'month'
        ));
    }

    /**
     * Prepara dados para os gráficos
     */
    private function prepareChartData($user, $year, $month)
    {
        $currentStart = Carbon::create($year, $month, 1)->startOfMonth();
        $currentEnd = Carbon::create($year, $month, 1)->endOfMonth();

        // Gráfico de gastos por categoria (Pizza)
        $categoryData = $this->expenseRepository->getTotalByCategory(
            $user,
            $currentStart,
            $currentEnd
        );

        $categoriesChart = [
            'labels' => $categoryData->pluck('category.name')->toArray(),
            'data' => $categoryData->pluck('total')->toArray(),
            'colors' => $categoryData->pluck('category.color')->toArray(),
        ];

        // Gráfico de evolução mensal (últimos 6 meses)
        $monthlyEvolution = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::create($year, $month, 1)->subMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            
            $total = $this->expenseRepository->getTotalByPeriod($user, $monthStart, $monthEnd);
            
            $monthlyEvolution['labels'][] = $monthStart->format('M/Y');
            $monthlyEvolution['data'][] = $total;
        }

        // Gráfico de status (Barras)
        $statusChart = [
            'labels' => ['Pago', 'Pendente', 'Vencido'],
            'data' => [
                $this->expenseRepository->getTotalByPeriod($user, $currentStart, $currentEnd, 'paid'),
                $this->expenseRepository->getTotalByPeriod($user, $currentStart, $currentEnd, 'pending'),
                $this->expenseRepository->getTotalByPeriod($user, $currentStart, $currentEnd, 'overdue'),
            ],
            'colors' => ['#10B981', '#F59E0B', '#EF4444'],
        ];

        return [
            'categories' => $categoriesChart,
            'monthly_evolution' => $monthlyEvolution,
            'status' => $statusChart,
        ];
    }

    /**
     * Retorna dados do dashboard em JSON (para AJAX)
     */
    public function getData(Request $request)
    {
        $user = Auth::user();
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $chartData = $this->prepareChartData($user, $year, $month);

        return response()->json($chartData);
    }

    /**
     * Atualiza status de despesas vencidas
     */
    public function updateOverdue()
    {
        $user = Auth::user();
        $updated = $this->expenseService->updateOverdueExpenses($user);

        return response()->json([
            'success' => true,
            'message' => "{$updated} despesas atualizadas",
        ]);
    }
}