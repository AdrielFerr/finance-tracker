@extends('layouts.app')

@section('title', 'Categorias')
@section('page-title', 'Minhas Categorias')

@section('content')
<div class="space-y-6" x-data="categoriesManager()" x-init="initSearch()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Categorias</h2>
            <p class="mt-1 text-sm text-gray-500 hidden sm:block">Gerencie suas categorias de despesas</p>
        </div>
        <a href="{{ route('categories.create') }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span class="hidden sm:inline">Nova Categoria</span>
            <span class="sm:hidden">+</span>
        </a>
    </div>

    <!-- Busca -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="max-w-md">
            <label for="search" class="block text-sm font-medium text-gray-700">Buscar Categoria</label>
            <input type="text" 
                   id="search" 
                   x-model="searchQuery"
                   @input="filterCategories()"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   placeholder="Digite o nome da categoria...">
        </div>
    </div>

    <!-- Grid de Categorias -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <template x-for="category in filteredCategories" :key="category.id">
            <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                <div class="p-5">
                    <!-- Header com cor -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-xl font-bold" :style="`background-color: ${category.color}`">
                                    <span x-text="category.name.charAt(0).toUpperCase()"></span>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900" x-text="category.name"></h3>
                                <p class="text-sm text-gray-500">
                                    <span x-text="category.expenses_count"></span> despesas
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Descrição -->
                    <template x-if="category.description">
                        <p class="text-sm text-gray-600 mb-4" x-text="category.description"></p>
                    </template>

                    <!-- Status -->
                    <div class="flex items-center justify-between mb-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                              :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                            <span x-text="category.is_active ? 'Ativa' : 'Inativa'"></span>
                        </span>
                        <div class="w-8 h-8 rounded" :style="`background-color: ${category.color}`"></div>
                    </div>

                    <!-- Ações -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <form :action="`/categories/${category.id}/toggle-status`" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                                <span x-text="category.is_active ? 'Desativar' : 'Ativar'"></span>
                            </button>
                        </form>
                        
                        <div class="flex items-center space-x-3">
                            <a :href="`/categories/${category.id}/edit`" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            
                            <button @click="deleteCategory(category.id, category.expenses_count)" type="button" class="text-red-600 hover:text-red-900" title="Excluir">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Estado vazio -->
        <template x-if="filteredCategories.length === 0">
            <div class="col-span-full">
                <div class="text-center py-12 bg-white rounded-lg shadow">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma categoria encontrada</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        <template x-if="searchQuery.length > 0">
                            <span>Tente buscar com outro termo</span>
                        </template>
                        <template x-if="searchQuery.length === 0">
                            <span>Comece criando uma nova categoria</span>
                        </template>
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('categories.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Nova Categoria
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
function categoriesManager() {
    return {
        categories: @json($categories),
        filteredCategories: [],
        searchQuery: '',
        
        initSearch() {
            this.filteredCategories = this.categories;
        },
        
        filterCategories() {
            const query = this.searchQuery.toLowerCase().trim();
            
            if (query === '') {
                this.filteredCategories = this.categories;
                return;
            }
            
            this.filteredCategories = this.categories.filter(category => {
                return category.name.toLowerCase().includes(query) ||
                       (category.description && category.description.toLowerCase().includes(query));
            });
        },
        
        deleteCategory(id, expensesCount) {
            if (expensesCount > 0) {
                alert('Esta categoria não pode ser excluída pois possui ' + expensesCount + ' despesa(s) associada(s).');
                return;
            }
            
            if (confirm('Tem certeza que deseja excluir esta categoria?')) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '/categories/' + id;
                
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