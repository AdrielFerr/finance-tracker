@extends('layouts.app')

@section('title', 'Relatório Comparativo')
@section('page-title', 'Relatório Comparativo')

@section('content')
<div class="space-y-6" x-data="comparativeReport()">
    <!-- Header -->
    <div>
        <a href="{{ route('reports.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Voltar
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Relatório Comparativo</h2>
        <p class="mt-1 text-sm text-gray-500">Compare dois períodos diferentes</p>
    </div>

    <!-- Seletor de Períodos -->
    <div class="bg-white shadow rounded-lg p-6">
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Período 1 -->
                <div class="border border-indigo-200 rounded-lg p-4 bg-indigo-50">
                    <h3 class="text-sm font-medium text-indigo-900 mb-3">Período 1</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Data Inicial</label>
                            <input type="date" 
                                   name="start_date_1" 
                                   value="{{ $startDate1->format('Y-m-d') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Data Final</label>
                            <input type="date" 
                                   name="end_date_1" 
                                   value="{{ $endDate1->format('Y-m-d') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Período 2 -->
                <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                    <h3 class="text-sm font-medium text-green-900 mb-3">Período 2</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Data Inicial</label>
                            <input type="date" 
                                   name="start_date_2" 
                                   value="{{ $startDate2->format('Y-m-d') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Data Final</label>
                            <input type="date" 
                                   name="end_date_2" 
                                   value="{{ $endDate2->format('Y-m-d') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-center">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-700">
                    Comparar Períodos
                </button>
            </div>
        </form>
    </div>

    <!-- Comparação de Totais -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Período 1 -->
        <div class="bg-white shadow rounded-lg p-6 border-t-4 border-indigo-500">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Período 1</h3>
            <p class="text-xs text-gray-400 mb-2">
                {{ $startDate1->format('d/m/Y') }} - {{ $endDate1->format('d/m/Y') }}
            </p>
            <p class="text-2xl font-bold text-indigo-600">
                R$ {{ number_format($period1['total'], 2, ',', '.') }}
            </p>
        </div>

        <!-- Diferença -->
        <div class="bg-white shadow rounded-lg p-6 border-t-4 border-gray-400">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Diferença</h3>
            @php
                $diff = $period2['total'] - $period1['total'];
                $percentage = $period1['total'] > 0 ? ($diff / $period1['total']) * 100 : 0;
            @endphp
            <p class="text-xs text-gray-400 mb-2">
                {{ $diff > 0 ? 'Aumento' : 'Redução' }}
            </p>
            <p class="text-2xl font-bold {{ $diff > 0 ? 'text-red-600' : 'text-green-600' }}">
                {{ $diff > 0 ? '+' : '' }}{{ number_format($percentage, 1) }}%
            </p>
            <p class="text-sm text-gray-500 mt-1">
                {{ $diff > 0 ? '+' : '' }}R$ {{ number_format(abs($diff), 2, ',', '.') }}
            </p>
        </div>

        <!-- Total Período 2 -->
        <div class="bg-white shadow rounded-lg p-6 border-t-4 border-green-500">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Período 2</h3>
            <p class="text-xs text-gray-400 mb-2">
                {{ $startDate2->format('d/m/Y') }} - {{ $endDate2->format('d/m/Y') }}
            </p>
            <p class="text-2xl font-bold text-green-600">
                R$ {{ number_format($period2['total'], 2, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- Gráfico Comparativo -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Comparação por Categoria</h3>
        <canvas id="comparisonChart"></canvas>
    </div>

    <!-- Tabelas Lado a Lado -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Top 5 Período 1 -->
        <div class="bg-white shadow rounded-lg p-6 border-t-4 border-indigo-500">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Top 5 - Período 1</h3>
            <div class="space-y-3">
                @foreach($period1['top_expenses'] as $expense)
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $expense->description }}</p>
                            <p class="text-xs text-gray-500">{{ $expense->category->name }}</p>
                        </div>
                        <span class="text-sm font-semibold text-indigo-600">
                            R$ {{ number_format($expense->amount, 2, ',', '.') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Top 5 Período 2 -->
        <div class="bg-white shadow rounded-lg p-6 border-t-4 border-green-500">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Top 5 - Período 2</h3>
            <div class="space-y-3">
                @foreach($period2['top_expenses'] as $expense)
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $expense->description }}</p>
                            <p class="text-xs text-gray-500">{{ $expense->category->name }}</p>
                        </div>
                        <span class="text-sm font-semibold text-green-600">
                            R$ {{ number_format($expense->amount, 2, ',', '.') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function comparativeReport() {
    return {
        init() {
            // Combinar categorias dos dois períodos
            const categories1 = @json($period1['by_category']->pluck('category_name'));
            const values1 = @json($period1['by_category']->pluck('total'));
            
            const categories2 = @json($period2['by_category']->pluck('category_name'));
            const values2 = @json($period2['by_category']->pluck('total'));
            
            // Unir todas as categorias únicas
            const allCategories = [...new Set([...categories1, ...categories2])];
            
            // Mapear valores para cada categoria
            const data1 = allCategories.map(cat => {
                const index = categories1.indexOf(cat);
                return index >= 0 ? values1[index] : 0;
            });
            
            const data2 = allCategories.map(cat => {
                const index = categories2.indexOf(cat);
                return index >= 0 ? values2[index] : 0;
            });
            
            // Gráfico Comparativo
            const ctx = document.getElementById('comparisonChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: allCategories,
                    datasets: [
                        {
                            label: 'Período 1',
                            data: data1,
                            backgroundColor: '#6366f1'
                        },
                        {
                            label: 'Período 2',
                            data: data2,
                            backgroundColor: '#10b981'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }
}
</script>
@endpush