@extends('layouts.app')

@section('title', 'Métodos de Pagamento')
@section('page-title', 'Métodos de Pagamento')

@section('content')
<div class="space-y-6" x-data="paymentMethodsManager()" x-init="initSearch()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Métodos de Pagamento</h2>
            <p class="mt-1 text-sm text-gray-500 hidden sm:block">Gerencie seus cartões, PIX, dinheiro e outros métodos</p>
        </div>
        <a href="{{ route('payment-methods.create') }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span class="hidden sm:inline">Novo Método</span>
            <span class="sm:hidden">+</span>
        </a>
    </div>

    <!-- Busca -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="max-w-md">
            <label for="search" class="block text-sm font-medium text-gray-700">Buscar Método</label>
            <input type="text" 
                   id="search" 
                   x-model="searchQuery"
                   @input="filterMethods()"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   placeholder="Digite o nome do cartão, PIX...">
        </div>
    </div>

    <!-- Grid de Métodos -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <template x-for="method in filteredMethods" :key="method.id">
            <div class="relative overflow-hidden rounded-xl shadow-lg transition-all hover:shadow-xl" 
                 :style="`background: linear-gradient(135deg, ${method.color} 0%, ${method.color}dd 100%)`">
                <!-- Card estilo cartão de crédito -->
                <div class="p-6 text-white">
                    <!-- Header com tipo e ações -->
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex items-center space-x-2">
                            <!-- Ícone do tipo -->
                            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <template x-if="method.type === 'credit_card'">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </template>
                                <template x-if="method.type === 'debit_card'">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </template>
                                <template x-if="method.type === 'pix'">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"></path>
                                    </svg>
                                </template>
                                <template x-if="method.type === 'cash'">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                                    </svg>
                                </template>
                                <template x-if="method.type === 'bank_transfer' || method.type === 'bank_slip'">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                    </svg>
                                </template>
                                <template x-if="method.type === 'other'">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                                    </svg>
                                </template>
                            </div>
                            
                            <div>
                                <p class="text-xs opacity-75" x-text="getTypeLabel(method.type)"></p>
                                <template x-if="method.brand">
                                    <p class="text-xs font-medium" x-text="method.brand"></p>
                                </template>
                            </div>
                        </div>
                        
                        <!-- Status badge -->
                        <span class="px-2 py-1 text-xs font-semibold rounded-full" 
                              :class="method.is_active ? 'bg-white bg-opacity-20' : 'bg-black bg-opacity-30'">
                            <span x-text="method.is_active ? 'Ativo' : 'Inativo'"></span>
                        </span>
                    </div>

                    <!-- Nome do método -->
                    <div class="mb-4">
                        <h3 class="text-xl font-bold" x-text="method.name"></h3>
                        <p class="text-sm opacity-75">
                            <span x-text="method.expenses_count"></span> despesas
                        </p>
                    </div>

                    <!-- Número do cartão (se tiver) -->
                    <template x-if="method.last_four_digits">
                        <div class="mb-4">
                            <p class="text-sm font-mono tracking-wider">
                                •••• •••• •••• <span x-text="method.last_four_digits"></span>
                            </p>
                        </div>
                    </template>

                    <!-- Detalhes específicos -->
                    <div class="flex items-center justify-between text-xs opacity-75">
                        <template x-if="method.type === 'credit_card' && method.due_day">
                            <span>Venc: Dia <span x-text="method.due_day"></span></span>
                        </template>
                        <template x-if="method.type === 'credit_card' && method.credit_limit">
                            <span>Limite: R$ <span x-text="formatMoney(method.credit_limit)"></span></span>
                        </template>
                    </div>
                </div>

                <!-- Ações (fora do gradiente) -->
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-100">
                    <form :action="`{{ url('payment-methods') }}/${method.id}/toggle-status`" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                            <span x-text="method.is_active ? 'Desativar' : 'Ativar'"></span>
                        </button>
                    </form>
                    
                    <div class="flex items-center space-x-3">
                        <a :href="`{{ url('payment-methods') }}/${method.id}/edit`" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                        
                        <button @click="deleteMethod(method.id, method.expenses_count)" type="button" class="text-red-600 hover:text-red-900" title="Excluir">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Estado vazio -->
        <template x-if="filteredMethods.length === 0">
            <div class="col-span-full">
                <div class="text-center py-12 bg-white rounded-lg shadow">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum método encontrado</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        <template x-if="searchQuery.length > 0">
                            <span>Tente buscar com outro termo</span>
                        </template>
                        <template x-if="searchQuery.length === 0">
                            <span>Comece adicionando um método de pagamento</span>
                        </template>
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('payment-methods.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Novo Método
                        </a>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
function paymentMethodsManager() {
    return {
        methods: @json($paymentMethods),
        filteredMethods: [],
        searchQuery: '',
        
        initSearch() {
            this.filteredMethods = this.methods;
        },
        
        filterMethods() {
            const query = this.searchQuery.toLowerCase().trim();
            
            if (query === '') {
                this.filteredMethods = this.methods;
                return;
            }
            
            this.filteredMethods = this.methods.filter(method => {
                return method.name.toLowerCase().includes(query) ||
                       (method.brand && method.brand.toLowerCase().includes(query)) ||
                       this.getTypeLabel(method.type).toLowerCase().includes(query);
            });
        },
        
        getTypeLabel(type) {
            const labels = {
                'credit_card': 'Cartão de Crédito',
                'debit_card': 'Cartão de Débito',
                'pix': 'PIX',
                'cash': 'Dinheiro',
                'bank_transfer': 'Transferência',
                'bank_slip': 'Boleto',
                'other': 'Outro'
            };
            return labels[type] || type;
        },
        
        formatMoney(value) {
            return parseFloat(value).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },
        
        deleteMethod(id, expensesCount) {
            if (expensesCount > 0) {
                alert('Este método não pode ser excluído pois possui ' + expensesCount + ' despesa(s) associada(s).');
                return;
            }
            
            if (confirm('Tem certeza que deseja excluir este método de pagamento?')) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url("payment-methods") }}/' + id;
                
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