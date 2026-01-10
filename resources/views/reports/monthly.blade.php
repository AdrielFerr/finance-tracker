@extends('layouts.app')

@section('title', 'Relatório Mensal')
@section('page-title', 'Relatório Mensal')

@section('content')
<div class="space-y-6">
    <!-- Header com Seleção de Período -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Relatório Mensal</h2>
                <p class="mt-1 text-sm text-gray-500">Análise detalhada com comparativo</p>
            </div>
            
            <!-- Seletor de Mês/Ano -->
            <form method="GET" action="{{ route('reports.monthly') }}" class="flex gap-2">
                <select name="month" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                        </option>
                    @endfor
                </select>
                <select name="year" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
                    Atualizar
                </button>
            </form>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total do Mês -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-indigo-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total do Mês</dt>
                            <dd class="text-lg font-semibold text-gray-900">R$ {{ number_format($stats['current_total'], 2, ',', '.') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagas -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-green-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Despesas Pagas</dt>
                            <dd class="text-lg font-semibold text-green-600">R$ {{ number_format($stats['current_paid'], 2, ',', '.') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pendentes -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-yellow-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pendentes</dt>
                            <dd class="text-lg font-semibold text-yellow-600">R$ {{ number_format($stats['current_pending'], 2, ',', '.') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparativo com Mês Anterior -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-purple-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">vs Mês Anterior</dt>
                            <dd class="flex items-baseline">
                                @php
                                    $diff = $stats['current_total'] - $stats['previous_total'];
                                    $percentChange = $stats['previous_total'] > 0 ? (($diff / $stats['previous_total']) * 100) : 0;
                                @endphp
                                <span class="text-lg font-semibold {{ $diff > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $diff > 0 ? '+' : '' }}{{ number_format($percentChange, 1) }}%
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos por Categoria e Método de Pagamento -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Despesas por Categoria -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Despesas por Categoria</h3>
            @if($byCategory->isNotEmpty())
                <div class="space-y-3">
                    @foreach($byCategory as $item)
                        @php
                            $percentage = ($item->total / $stats['current_total']) * 100;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700">{{ $item->category->name }}</span>
                                <span class="text-gray-600">R$ {{ number_format($item->total, 2, ',', '.') }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500">{{ number_format($percentage, 1) }}%</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Nenhuma despesa neste período</p>
            @endif
        </div>

        <!-- Despesas por Método de Pagamento -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Métodos de Pagamento</h3>
            @if($byPaymentMethod->isNotEmpty())
                <div class="space-y-3">
                    @foreach($byPaymentMethod as $item)
                        @php
                            $percentage = ($item->total / $stats['current_total']) * 100;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700">{{ $item->paymentMethod->name ?? 'Não informado' }}</span>
                                <span class="text-gray-600">R$ {{ number_format($item->total, 2, ',', '.') }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500">{{ number_format($percentage, 1) }}%</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Nenhuma despesa neste período</p>
            @endif
        </div>
    </div>

    <!-- Maiores Despesas do Mês -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Maiores Despesas do Mês</h3>
        @if($topExpenses->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($topExpenses as $expense)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $expense->due_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $expense->description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $expense->category->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($expense->status === 'paid')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Pago
                                        </span>
                                    @elseif($expense->status === 'pending')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pendente
                                        </span>
                                    @elseif($expense->status === 'overdue')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Vencido
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                    R$ {{ number_format($expense->amount, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-center py-4">Nenhuma despesa neste período</p>
        @endif
    </div>

    <!-- Média Diária -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Média Diária de Gastos</h3>
                <p class="mt-1 text-sm text-gray-600">Baseado nas despesas do mês</p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-indigo-600">
                    R$ {{ number_format($dailyAverage, 2, ',', '.') }}
                </p>
                <p class="text-sm text-gray-500">por dia</p>
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="flex gap-3">
        <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar
        </a>
        
        <a href="{{ route('reports.export-pdf', ['start_month' => $month, 'start_year' => $year, 'end_month' => $month, 'end_year' => $year]) }}" 
           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            Exportar PDF
        </a>
    </div>
</div>
@endsection