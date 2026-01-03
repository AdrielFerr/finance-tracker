@extends('layouts.app')

@section('title', 'Despesas')
@section('page-title', 'Minhas Despesas')

@section('content')
<div class="space-y-6" x-data="expensesManager()" x-init="initSearch()">
    <!-- Header com botão de nova despesa -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Despesas</h2>
            <p class="mt-1 text-sm text-gray-500 hidden sm:block">Gerencie todas as suas despesas</p>
        </div>
        <div class="flex items-center space-x-2 sm:space-x-3">
            <!-- Botão de exclusão em massa -->
            <div class="relative group">
                <button 
                    x-show="selected.length > 0" 
                    @click="deleteSelected()"
                    x-cloak
                    type="button"
                    class="inline-flex items-center px-3 sm:px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span class="hidden sm:inline">Excluir <span x-text="selected.length"></span> selecionada(s)</span>
                    <span class="sm:hidden" x-text="selected.length"></span>
                </button>
                <!-- Tooltip Mobile -->
                <div class="sm:hidden absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50">
                    Excluir selecionadas
                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                </div>
            </div>

            <div class="relative group">
                <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span class="hidden sm:inline">Nova Despesa</span>
                    <span class="sm:hidden">+</span>
                </a>
                <!-- Tooltip Mobile -->
                <div class="sm:hidden absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50">
                    Nova Despesa
                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-2">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total do Período</dt>
                            <dd class="text-lg font-semibold text-gray-900">R$ {{ number_format($stats['total_amount'], 2, ',', '.') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total de Despesas</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['total'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white shadow rounded-lg p-6">
        <form method="GET" action="{{ route('expenses.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                <!-- Busca -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Buscar</label>
                    <input type="text" 
                           name="search" 
                           id="search" 
                           x-model="searchQuery"
                           @input.debounce.800ms="performSearch()"
                           value="{{ request('search') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="Digite para buscar...">
                    <p class="mt-1 text-xs text-gray-500" x-show="searchQuery.length > 0" x-cloak>
                        Buscando por: "<span x-text="searchQuery"></span>"
                    </p>
                </div>

                <!-- Data de Vencimento -->
                <div class="lg:col-span-2">
                    <label for="due_date" class="block text-sm font-medium text-gray-700">Data de Vencimento</label>
                    <input type="date" name="due_date" id="due_date" value="{{ request('due_date') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="dd/mm/aaaa">
                    <p class="mt-1 text-xs text-gray-500">Ex: 29/12/2025 - mostra despesas deste dia</p>
                </div>

                <!-- Categoria -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Categoria</label>
                    <select name="category_id" id="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todas</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todos</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Pago</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Vencido</option>
                    </select>
                </div>

                <!-- Tipo -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Tipo</label>
                    <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todos</option>
                        <option value="fixed" {{ request('type') == 'fixed' ? 'selected' : '' }}>Fixa</option>
                        <option value="variable" {{ request('type') == 'variable' ? 'selected' : '' }}>Variável</option>
                        <option value="occasional" {{ request('type') == 'occasional' ? 'selected' : '' }}>Eventual</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Filtrar
                    </button>
                    <a href="{{ route('expenses.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                        Limpar
                    </a>
                </div>

                <div class="text-sm text-gray-500 hidden sm:block">
                    Mostrando {{ $expenses->firstItem() ?? 0 }} - {{ $expenses->lastItem() ?? 0 }} de {{ $expenses->total() }} despesas
                </div>
            </div>
        </form>
    </div>

    <!-- Tabela de Despesas -->
    <div class="bg-white shadow rounded-lg overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">
                        <input type="checkbox" 
                               @change="toggleAll($event.target.checked)"
                               :checked="selected.length === {{ $expenses->count() }} && {{ $expenses->count() }} > 0"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($expenses as $expense)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" 
                                   value="{{ $expense->id }}"
                                   @change="toggleSelect({{ $expense->id }})"
                                   :checked="selected.includes({{ $expense->id }})"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $expense->description }}</div>
                            @if($expense->notes)
                                <div class="text-sm text-gray-500">{{ Str::limit($expense->notes, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: {{ $expense->category->color }}20; color: {{ $expense->category->color }}">
                                {{ $expense->category->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">R$ {{ number_format($expense->amount, 2, ',', '.') }}</div>
                            @if($expense->paymentMethod)
                                <div class="text-xs text-gray-500">{{ $expense->paymentMethod->name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $expense->due_date->format('d/m/Y') }}</div>
                            @if($expense->payment_date)
                                <div class="text-xs text-green-600">Pago em {{ $expense->payment_date->format('d/m/Y') }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($expense->status == 'paid') bg-green-100 text-green-800
                                @elseif($expense->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($expense->status == 'overdue') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $expense->status_text }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                @if($expense->status != 'paid')
                                    <form method="POST" action="{{ route('expenses.mark-paid', $expense) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900" title="Marcar como paga">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('expenses.edit', $expense) }}" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>

                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta despesa?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Excluir">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="mt-2">Nenhuma despesa encontrada</p>
                            <a href="{{ route('expenses.create') }}" class="mt-4 inline-flex items-center text-indigo-600 hover:text-indigo-500">
                                Criar sua primeira despesa
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if($expenses->hasPages())
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg shadow">
            {{ $expenses->links() }}
        </div>
    @endif

    <!-- Form oculto para exclusão em massa -->
    <form id="bulkDeleteForm" method="POST" action="{{ route('expenses.bulk-delete') }}" style="display: none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="ids" id="selectedIds">
    </form>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection

@push('scripts')
<script>
function expensesManager() {
    return {
        selected: [],
        searchQuery: '{{ request('search') }}',
        searchTimeout: null,
        
        initSearch() {
            // Inicializar busca com valor do request
            this.searchQuery = '{{ request('search') }}' || '';
        },
        
        performSearch() {
            // Pegar todos os parâmetros atuais da URL
            const params = new URLSearchParams(window.location.search);
            
            // Atualizar o parâmetro de busca
            if (this.searchQuery.trim().length > 0) {
                params.set('search', this.searchQuery);
            } else {
                params.delete('search');
            }
            
            // Redirecionar com os novos parâmetros
            window.location.href = '{{ route('expenses.index') }}?' + params.toString();
        },
        
        toggleSelect(id) {
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(item => item !== id);
            } else {
                this.selected.push(id);
            }
        },
        
        toggleAll(checked) {
            if (checked) {
                this.selected = [
                    @foreach($expenses as $expense)
                        {{ $expense->id }},
                    @endforeach
                ];
            } else {
                this.selected = [];
            }
        },
        
        deleteSelected() {
            if (this.selected.length === 0) {
                alert('Selecione pelo menos uma despesa');
                return;
            }
            
            if (confirm(`Tem certeza que deseja excluir ${this.selected.length} despesa(s)?`)) {
                document.getElementById('selectedIds').value = this.selected.join(',');
                document.getElementById('bulkDeleteForm').submit();
            }
        },
        
        deleteSingle(id) {
            if (confirm('Tem certeza que deseja excluir esta despesa?')) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '/expenses/' + id;
                
                let csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                
                let method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                
                form.appendChild(csrf);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        }
    }
}
</script>
@endpush