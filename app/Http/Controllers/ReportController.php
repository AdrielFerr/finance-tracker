<?php

namespace App\Http\Controllers;

use App\Repositories\ExpenseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use PDF; // Caso instale barryvdh/laravel-dompdf

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

        $stats = [
            'total_year' => $this->expenseRepository->getTotalByPeriod($user, $yearStart, $yearEnd),
            'average_month' => $this->expenseRepository->getTotalByPeriod($user, $yearStart, $yearEnd) / 12,
            'by_category' => $this->expenseRepository->getTotalByCategory($user, $yearStart, $yearEnd),
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
            'by_category' => $this->expenseRepository->getTotalByCategory($user, $startDate1, $endDate1),
            'top_expenses' => $this->expenseRepository->getTopExpenses($user, $startDate1, $endDate1, 5),
        ];

        $period2 = [
            'total' => $this->expenseRepository->getTotalByPeriod($user, $startDate2, $endDate2),
            'by_category' => $this->expenseRepository->getTotalByCategory($user, $startDate2, $endDate2),
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
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $type = $request->input('type', 'monthly');
        
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $data = [
            'user' => $user,
            'year' => $year,
            'month' => $month,
            'expenses' => $this->expenseRepository->getMonthlyExpenses($user, $year, $month),
            'stats' => [
                'total' => $this->expenseRepository->getTotalByPeriod($user, $startDate, $endDate),
                'paid' => $this->expenseRepository->getTotalByPeriod($user, $startDate, $endDate, 'paid'),
                'pending' => $this->expenseRepository->getTotalByPeriod($user, $startDate, $endDate, 'pending'),
            ],
            'by_category' => $this->expenseRepository->getTotalByCategory($user, $startDate, $endDate),
        ];

        // Se você instalou o dompdf:
        // $pdf = PDF::loadView('reports.pdf.monthly', $data);
        // return $pdf->download("relatorio-{$year}-{$month}.pdf");

        // Por enquanto, retornar view
        return view('reports.pdf.monthly', $data);
    }

    /**
     * Exportar para Excel/CSV
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        $expenses = $this->expenseRepository->getMonthlyExpenses($user, $year, $month);

        // Gerar CSV
        $filename = "despesas-{$year}-{$month}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($expenses) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalho
            fputcsv($file, [
                'Data Vencimento',
                'Descrição',
                'Categoria',
                'Valor',
                'Status',
                'Método Pagamento',
                'Data Pagamento'
            ], ';');

            // Dados
            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->due_date->format('d/m/Y'),
                    $expense->description,
                    $expense->category->name,
                    number_format($expense->amount, 2, ',', '.'),
                    $expense->status_text,
                    $expense->paymentMethod?->name ?? '-',
                    $expense->payment_date ? $expense->payment_date->format('d/m/Y') : '-',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}