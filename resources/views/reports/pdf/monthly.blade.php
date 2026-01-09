<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Mensal - {{ $monthName }} {{ $year }}</title>
    <style>
        @page {
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }

        .content {
            padding: 0 5mm;
        }

        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-item {
            display: table-cell;
            width: 33.33%;
            padding: 15px;
            border: 1px solid #e5e7eb;
            text-align: center;
        }

        .summary-item .label {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .summary-item .value {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
        }

        .summary-item.paid .value {
            color: #10b981;
        }

        .summary-item.pending .value {
            color: #f59e0b;
        }

        .summary-item.overdue .value {
            color: #ef4444;
        }

        h2 {
            font-size: 16px;
            margin: 20px 0 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #4F46E5;
            color: #1f2937;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead {
            background-color: #f3f4f6;
        }

        table th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }

        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }

        table tbody tr:hover {
            background-color: #f9fafb;
        }

        .status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }

        .status.paid {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status.pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status.overdue {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .category-summary {
            margin-top: 30px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <div class="header">
        <h1>{{ $periodSubtitle ?? 'Relatório Mensal de Despesas' }}</h1>
        <p>{{ $periodTitle ?? $monthName . ' de ' . $year }}</p>
    </div>

    <div class="content">
        <!-- Resumo -->
        <div class="summary">
            <div class="summary-item">
                <div class="label">Total Geral</div>
                <div class="value">R$ {{ number_format($totalAmount, 2, ',', '.') }}</div>
            </div>
            <div class="summary-item paid">
                <div class="label">Pagas</div>
                <div class="value">R$ {{ number_format($paidAmount, 2, ',', '.') }}</div>
            </div>
            <div class="summary-item pending">
                <div class="label">Pendentes</div>
                <div class="value">R$ {{ number_format($pendingAmount, 2, ',', '.') }}</div>
            </div>
        </div>

        <!-- Lista de Despesas Agrupadas por Mês -->
        <h2>Despesas do Período</h2>
        
        @php
            // Agrupar despesas por mês
            $expensesByMonth = $expenses->groupBy(function($expense) {
                return $expense->due_date->format('Y-m');
            })->sortKeys();
            
            $monthNames = [
                '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
            ];
        @endphp

        @forelse($expensesByMonth as $yearMonth => $monthExpenses)
            @php
                [$year, $month] = explode('-', $yearMonth);
                $monthName = $monthNames[$month];
                $monthTotal = $monthExpenses->sum('amount');
            @endphp
            
            <!-- Título do Mês -->
            <div style="margin-top: 25px; margin-bottom: 10px; padding: 10px; background-color: #f3f4f6; border-left: 4px solid #4F46E5;">
                <strong style="font-size: 13px; color: #1f2937;">{{ $monthName }}/{{ $year }}</strong>
                <span style="float: right; font-size: 12px; color: #6b7280;">
                    Total: R$ {{ number_format($monthTotal, 2, ',', '.') }}
                </span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthExpenses as $expense)
                        <tr>
                            <td>{{ $expense->due_date->format('d/m/Y') }}</td>
                            <td>{{ $expense->description }}</td>
                            <td>{{ $expense->category->name }}</td>
                            <td class="text-center">
                                <span class="status {{ $expense->status }}">
                                    @if($expense->status === 'paid') Pago
                                    @elseif($expense->status === 'pending') Pendente
                                    @elseif($expense->status === 'overdue') Vencido
                                    @else Cancelado
                                    @endif
                                </span>
                            </td>
                            <td class="text-right">R$ {{ number_format($expense->amount, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @empty
            <p style="text-align: center; padding: 20px; color: #6b7280;">
                Nenhuma despesa encontrada neste período.
            </p>
        @endforelse

        <!-- Resumo por Categoria -->
        @if($expensesByCategory->isNotEmpty())
            <div class="category-summary">
                <h2>Gastos por Categoria</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">% do Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expensesByCategory as $item)
                            <tr>
                                <td>{{ $item->category->name }}</td>
                                <td class="text-right">R$ {{ number_format($item->total, 2, ',', '.') }}</td>
                                <td class="text-right">{{ number_format(($item->total / $totalAmount) * 100, 1) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Rodapé -->
        <div class="footer">
            <p>Relatório gerado em {{ now()->format('d/m/Y H:i') }}</p>
            <p>FinanceTracker - Sistema de Gestão Financeira</p>
        </div>
    </div>
</body>
</html>