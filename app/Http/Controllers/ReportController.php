<?php

namespace App\Http\Controllers;

use App\Repositories\ExpenseRepository;
use App\Models\Expense; // ✅ ADICIONAR ESTA LINHA
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function __construct(
        private ExpenseRepository $expenseRepository
    ) {}

    /**
     * Página principal de relatórios
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Relatório mensal
     */
    public function monthly(Request $request)
    {
        $user = Auth::user();
        
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        // Período anterior para comparação
        $previousStart = $startDate->copy()->subMonth()->startOfMonth();
        $previousEnd = $startDate->copy()->subMonth()->endOfMonth();

        // Estatísticas
        $stats = [
            'current_total' => $this->expenseRepository->getTotalByPeriod($user, $startDate, $endDate),
            'current_paid' => $this->expenseRepository->getTotalByPeriod($user, $startDate, $endDate, 'paid'),
            'current_pending' => $this->expenseRepository->getTotalByPeriod($user, $startDate, $endDate, 'pending'),
            'previous_total' => $this->expenseRepository->getTotalByPeriod($user, $previousStart, $previousEnd),
        ];

        // Comparativo
        $comparison = $this->expenseRepository->getComparativeStats(
            $user,
            $startDate,
            $endDate,
            $previousStart,
            $previousEnd
        );

        // Despesas do mês
        $expenses = $this->expenseRepository->getMonthlyExpenses($user, $year, $month);

        // Por categoria
        $byCategory = $this->expenseRepository->getTotalByCategory($user, $startDate, $endDate);

        // Por método de pagamento
        $byPaymentMethod = $this->expenseRepository->getTotalByPaymentMethod($user, $startDate, $endDate);

        // Maiores despesas
        $topExpenses = $this->expenseRepository->getTopExpenses($user, $startDate, $endDate, 10);

        // Média diária
        $dailyAverage = $this->expenseRepository->getDailyAverage($user, $startDate, $endDate);

        return view('reports.monthly', compact(
            'year',
            'month',
            'stats',
            'comparison',
            'expenses',
            'byCategory',
            'byPaymentMethod',
            'topExpenses',
            'dailyAverage'
        ));
    }

    /**
     * Relatório anual
     */
    public function annual(Request $request)
    {
        $user = Auth::user();
        $year = $request->input('year', Carbon::now()->year);

        $monthlyData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            
            $monthlyData[] = [
                'month' => $month,
                'month_name' => $startDate->format('F'),
                'total' => $this->expenseRepository->getTotalByPeriod($user, $startDate, $endDate),
                'paid' => $this->expenseRepository->getTotalByPeriod($user, $startDate, $endDate, 'paid'),
                'pending' => $this->expenseRepository->getTotalByPeriod($user, $startDate, $endDate, 'pending'),
            ];
        }

        $yearStart = Carbon::create($year, 1, 1)->startOfYear();
        $yearEnd = Carbon::create($year, 12, 31)->endOfYear();

        $byCategory = $this->expenseRepository->getTotalByCategory($user, $yearStart, $yearEnd);
        
        $stats = [
            'total_year' => $this->expenseRepository->getTotalByPeriod($user, $yearStart, $yearEnd),
            'average_month' => $this->expenseRepository->getTotalByPeriod($user, $yearStart, $yearEnd) / 12,
            'by_category' => $byCategory->map(function($item) {
                return [
                    'category_name' => $item->category->name,
                    'category_color' => $item->category->color ?? '#6366f1',
                    'total' => $item->total
                ];
            }),
        ];

        return view('reports.annual', compact('year', 'monthlyData', 'stats'));
    }

    /**
     * Relatório comparativo (entre períodos)
     */
    public function comparative(Request $request)
    {
        $user = Auth::user();

        $startDate1 = Carbon::parse($request->input('start_date_1', now()->subMonth()->startOfMonth()));
        $endDate1 = Carbon::parse($request->input('end_date_1', now()->subMonth()->endOfMonth()));
        
        $startDate2 = Carbon::parse($request->input('start_date_2', now()->startOfMonth()));
        $endDate2 = Carbon::parse($request->input('end_date_2', now()->endOfMonth()));

        $period1 = [
            'total' => $this->expenseRepository->getTotalByPeriod($user, $startDate1, $endDate1),
            'by_category' => $this->expenseRepository->getTotalByCategory($user, $startDate1, $endDate1)->map(function($item) {
                return [
                    'category_name' => $item->category->name,
                    'total' => $item->total
                ];
            }),
            'top_expenses' => $this->expenseRepository->getTopExpenses($user, $startDate1, $endDate1, 5),
        ];

        $period2 = [
            'total' => $this->expenseRepository->getTotalByPeriod($user, $startDate2, $endDate2),
            'by_category' => $this->expenseRepository->getTotalByCategory($user, $startDate2, $endDate2)->map(function($item) {
                return [
                    'category_name' => $item->category->name,
                    'total' => $item->total
                ];
            }),
            'top_expenses' => $this->expenseRepository->getTopExpenses($user, $startDate2, $endDate2, 5),
        ];

        return view('reports.comparative', compact(
            'startDate1',
            'endDate1',
            'startDate2',
            'endDate2',
            'period1',
            'period2'
        ));
    }

    /**
     * Exportar relatório em PDF
     * ✅ MODIFICADO - Agora aceita intervalo de datas
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        
        // ✅ Aceita intervalo de datas
        $startMonth = $request->input('start_month', Carbon::now()->month);
        $startYear = $request->input('start_year', Carbon::now()->year);
        $endMonth = $request->input('end_month', Carbon::now()->month);
        $endYear = $request->input('end_year', Carbon::now()->year);
        
        // Criar datas de início e fim
        $startDate = Carbon::create($startYear, $startMonth, 1)->startOfMonth();
        $endDate = Carbon::create($endYear, $endMonth, 1)->endOfMonth();

        // Buscar despesas do período
        $expenses = Expense::with(['category', 'paymentMethod'])
            ->where('user_id', $user->id)
            ->whereBetween('competence_date', [$startDate, $endDate])
            ->orderBy('due_date')
            ->get();

        // Calcular totais
        $totalAmount = $expenses->sum('amount');
        $paidAmount = $expenses->where('status', 'paid')->sum('amount');
        $pendingAmount = $expenses->where('status', 'pending')->sum('amount');

        // Agrupar por categoria
        $expensesByCategory = Expense::selectRaw('category_id, SUM(amount) as total')
            ->with('category')
            ->where('user_id', $user->id)
            ->whereBetween('competence_date', [$startDate, $endDate])
            ->where('status', '!=', 'canceled')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();

        // Formatar período
        $monthNames = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',
            4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
            7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro',
            10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];

        $startMonthName = $monthNames[$startMonth];
        $endMonthName = $monthNames[$endMonth];

        // Título do período
        if ($startMonth == $endMonth && $startYear == $endYear) {
            // Mesmo mês
            $periodTitle = "{$startMonthName} de {$startYear}";
            $periodSubtitle = "Relatório Mensal de Despesas";
        } else {
            // Intervalo
            $periodTitle = "{$startMonthName}/{$startYear} até {$endMonthName}/{$endYear}";
            $periodSubtitle = "Relatório do Período";
        }

        $data = [
            'user' => $user,
            'expenses' => $expenses,
            'totalAmount' => $totalAmount,
            'paidAmount' => $paidAmount,
            'pendingAmount' => $pendingAmount,
            'expensesByCategory' => $expensesByCategory,
            'periodTitle' => $periodTitle,
            'periodSubtitle' => $periodSubtitle,
            // Compatibilidade com view antiga
            'monthName' => $periodTitle,
            'year' => $startYear,
            'month' => $startMonth,
        ];

        // Gerar PDF
        $pdf = Pdf::loadView('reports.pdf.monthly', $data);
        
        // Nome do arquivo
        if ($startMonth == $endMonth && $startYear == $endYear) {
            $fileName = "relatorio-{$startMonthName}-{$startYear}.pdf";
        } else {
            $fileName = "relatorio-{$startMonthName}-{$startYear}-ate-{$endMonthName}-{$endYear}.pdf";
        }

        return $pdf->download($fileName);
    }

    /**
     * Exportar para Excel/CSV
     * ✅ MODIFICADO - Agora aceita intervalo de datas
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        
        // ✅ Aceita intervalo de datas
        $startMonth = $request->input('start_month', Carbon::now()->month);
        $startYear = $request->input('start_year', Carbon::now()->year);
        $endMonth = $request->input('end_month', Carbon::now()->month);
        $endYear = $request->input('end_year', Carbon::now()->year);
        
        // Criar datas de início e fim
        $startDate = Carbon::create($startYear, $startMonth, 1)->startOfMonth();
        $endDate = Carbon::create($endYear, $endMonth, 1)->endOfMonth();

        // Buscar despesas do período
        $expenses = Expense::with(['category', 'paymentMethod'])
            ->where('user_id', $user->id)
            ->whereBetween('competence_date', [$startDate, $endDate])
            ->orderBy('due_date')
            ->get();

        // Nome do mês em português
        $monthNames = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'marco',
            4 => 'abril', 5 => 'maio', 6 => 'junho',
            7 => 'julho', 8 => 'agosto', 9 => 'setembro',
            10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
        ];

        $startMonthName = $monthNames[$startMonth];
        $endMonthName = $monthNames[$endMonth];

        // Nome do arquivo
        if ($startMonth == $endMonth && $startYear == $endYear) {
            $filename = "despesas-{$startMonthName}-{$startYear}.csv";
        } else {
            $filename = "despesas-{$startMonthName}-{$startYear}-ate-{$endMonthName}-{$endYear}.csv";
        }
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($expenses, $monthNames) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalho
            fputcsv($file, [
                'Mês/Ano',
                'Data Vencimento',
                'Descrição',
                'Categoria',
                'Valor',
                'Status',
                'Método Pagamento',
                'Data Pagamento'
            ], ';');

            // Agrupar despesas por mês
            $expensesByMonth = $expenses->groupBy(function($expense) {
                return $expense->due_date->format('Y-m');
            })->sortKeys();

            // Dados agrupados por mês
            foreach ($expensesByMonth as $yearMonth => $monthExpenses) {
                $monthYear = \Carbon\Carbon::parse($yearMonth . '-01')->format('m/Y');
                $monthTotal = $monthExpenses->sum('amount');
                
                // Linha de cabeçalho do mês (em destaque)
                fputcsv($file, [
                    '',
                    '',
                    ">>> {$monthNames[\Carbon\Carbon::parse($yearMonth . '-01')->format('n')]}/{$yearMonth}",
                    '',
                    '',
                    '',
                    '',
                    ''
                ], ';');
                
                // Despesas do mês
                foreach ($monthExpenses as $expense) {
                    fputcsv($file, [
                        $monthYear,
                        $expense->due_date->format('d/m/Y'),
                        $expense->description,
                        $expense->category->name,
                        number_format($expense->amount, 2, ',', '.'),
                        match($expense->status) {
                            'paid' => 'Pago',
                            'pending' => 'Pendente',
                            'overdue' => 'Vencido',
                            'canceled' => 'Cancelado',
                            default => $expense->status
                        },
                        $expense->paymentMethod?->name ?? '-',
                        $expense->payment_date ? $expense->payment_date->format('d/m/Y') : '-',
                    ], ';');
                }
                
                // Linha de subtotal do mês
                fputcsv($file, [
                    '',
                    '',
                    'SUBTOTAL ' . $monthNames[\Carbon\Carbon::parse($yearMonth . '-01')->format('n')],
                    '',
                    number_format($monthTotal, 2, ',', '.'),
                    '',
                    '',
                    ''
                ], ';');
                
                // Linha em branco para separar meses
                fputcsv($file, ['', '', '', '', '', '', '', ''], ';');
            }
            
            // Total geral
            $totalGeral = $expenses->sum('amount');
            fputcsv($file, [
                '',
                '',
                'TOTAL GERAL',
                '',
                number_format($totalGeral, 2, ',', '.'),
                '',
                '',
                ''
            ], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}