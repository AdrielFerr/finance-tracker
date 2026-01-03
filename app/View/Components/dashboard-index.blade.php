@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Financeiro')

@section('content')
<div class="space-y-6" x-data="dashboardData()">
    <!-- Filtro de Período -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <select x-model="selectedMonth" @change="loadData()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                @endfor
            </select>
            <select x-model="selectedYear" @change="loadData()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total de Despesas -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-indigo-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Total de Despesas</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    R$ {{ number_format($currentStats['total'], 2, ',', '.') }}
                                </div>
                                @if($comparison['percentage_change'] != 0)
                                    <div class="ml-2 flex items-baseline text-sm font-semibold {{ $comparison['is_increase'] ? 'text-red-600' : 'text-green-600' }}">
                                        @if($comparison['is_increase'])
                                            <svg class="h-5 w-5 flex-shrink-0 self-center text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 17a.75.75 0 01-.75-.75V5.612L5.29 9.77a.75.75 0 01-1.08-1.04l5.25-5.5a.75.75 0 011.08 0l5.25 5.5a.75.75 0 11-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0110 17z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 flex-shrink-0 self-center text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.638l3.96-4.158a.75.75 0 111.08 1.04l-5.25 5.5a.75.75 0 01-1.08 0l-5.25-5.5a.75.75 0 111.08-1.04l3.96 4.158V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                        <span class="ml-1">{{ abs($comparison['percentage_change']) }}%</span>
                                    </div>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Despesas Pagas -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-green-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Pagas</dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                R$ {{ number_format($currentStats['paid'], 2, ',', '.') }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Despesas Pendentes -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-yellow-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Pendentes</dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                R$ {{ number_format($currentStats['pending'], 2, ',', '.') }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Despesas Vencidas -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-red-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Vencidas</dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                R$ {{ number_format($currentStats['overdue'], 2, ',', '.') }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Gráfico de Categorias (Pizza) -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Gastos por Categoria</h3>
                <div class="h-64">
                    <canvas id="categoriesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de Evolução (Linha) -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Evolução Mensal</h3>
                <div class="h-64">
                    <canvas id="evolutionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas e Próximas Despesas -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Despesas Vencidas -->
        @if($overdueExpenses->count() > 0)
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 flex items-center">
                    <svg class="h-5 w-5 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Despesas Vencidas ({{ $overdueExpenses->count() }})
                </h3>
                <ul class="divide-y divide-gray-200">
                    @foreach($overdueExpenses->take(5) as $expense)
                    <li class="py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $expense->description }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Vencimento: {{ $expense->due_date->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <span class="text-sm font-semibold text-red-600">
                                    R$ {{ number_format($expense->amount, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <!-- Próximas Despesas -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Próximas Despesas (7 dias)</h3>
                <ul class="divide-y divide-gray-200">
                    @forelse($upcomingExpenses->take(5) as $expense)
                    <li class="py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $expense->description }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Vencimento: {{ $expense->due_date->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <span class="text-sm font-semibold text-gray-900">
                                    R$ {{ number_format($expense->amount, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="py-3 text-sm text-gray-500">Nenhuma despesa nos próximos 7 dias</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Maiores Despesas -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Maiores Despesas do Mês</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($topExpenses as $expense)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $expense->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                      style="background-color: {{ $expense->category->color }}20; color: {{ $expense->category->color }}">
                                    {{ $expense->category->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $expense->due_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                R$ {{ number_format($expense->amount, 2, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function dashboardData() {
    return {
        selectedMonth: {{ $month }},
        selectedYear: {{ $year }},
        charts: {},

        init() {
            this.initCharts();
        },

        loadData() {
            // Recarregar página com novos parâmetros
            window.location.href = `{{ route('dashboard') }}?month=${this.selectedMonth}&year=${this.selectedYear}`;
        },

        initCharts() {
            // Gráfico de Categorias
            const categoriesCtx = document.getElementById('categoriesChart');
            if (categoriesCtx) {
                this.charts.categories = new Chart(categoriesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($chartData['categories']['labels']),
                        datasets: [{
                            data: @json($chartData['categories']['data']),
                            backgroundColor: @json($chartData['categories']['colors']),
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            }

            // Gráfico de Evolução
            const evolutionCtx = document.getElementById('evolutionChart');
            if (evolutionCtx) {
                this.charts.evolution = new Chart(evolutionCtx, {
                    type: 'line',
                    data: {
                        labels: @json($chartData['monthly_evolution']['labels']),
                        datasets: [{
                            label: 'Gastos Mensais',
                            data: @json($chartData['monthly_evolution']['data']),
                            borderColor: 'rgb(99, 102, 241)',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'R$ ' + value.toLocaleString('pt-BR');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    }
}
</script>
@endpush
