@extends('layouts.app')

@section('title', 'Editar Categoria')
@section('page-title', 'Editar Categoria')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('categories.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Voltar para categorias
        </a>
    </div>

    <div class="bg-white shadow rounded-lg" x-data="categoryForm()">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Editar Categoria</h3>
            <p class="mt-1 text-sm text-gray-500">Atualize os dados da categoria</p>
        </div>

        <form action="{{ route('categories.update', $category) }}" method="POST" class="px-6 py-5 space-y-6">
            @csrf
            @method('PUT')

            <!-- Nome -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Nome <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $category->name) }}"
                       required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-300 @enderror"
                       placeholder="Ex: Alimentação, Transporte, Moradia...">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Cor -->
            <div>
                <label for="color" class="block text-sm font-medium text-gray-700">
                    Cor <span class="text-red-500">*</span>
                </label>
                <div class="mt-2 flex items-center space-x-3">
                    <input type="color" 
                           name="color" 
                           id="color" 
                           x-model="selectedColor"
                           value="{{ old('color', $category->color) }}"
                           class="h-10 w-20 rounded border-gray-300 cursor-pointer">
                    
                    <input type="text" 
                           x-model="selectedColor"
                           @input="validateColor()"
                           class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('color') border-red-300 @enderror"
                           placeholder="#6366f1">
                    
                    <div class="w-10 h-10 rounded-lg border-2 border-gray-200" :style="`background-color: ${selectedColor}`"></div>
                </div>
                
                <!-- Cores sugeridas -->
                <div class="mt-3">
                    <p class="text-xs text-gray-500 mb-2">Cores sugeridas:</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="color in suggestedColors" :key="color">
                            <button type="button"
                                    @click="selectedColor = color"
                                    class="w-8 h-8 rounded-full border-2 hover:scale-110 transition-transform"
                                    :class="selectedColor === color ? 'border-gray-900' : 'border-gray-200'"
                                    :style="`background-color: ${color}`"
                                    :title="color">
                            </button>
                        </template>
                    </div>
                </div>
                
                @error('color')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Descrição -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">
                    Descrição
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('description') border-red-300 @enderror"
                          placeholder="Descrição opcional da categoria...">{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       id="is_active" 
                       value="1"
                       {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                    Categoria ativa
                </label>
            </div>

            <!-- Info de despesas -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Esta categoria possui <strong>{{ $category->expenses_count }}</strong> despesa(s) associada(s).
                            @if($category->expenses_count > 0)
                                <br><span class="text-xs">Não é possível excluir categorias com despesas.</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <p class="text-sm font-medium text-gray-700 mb-3">Preview:</p>
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-xl font-bold" 
                         :style="`background-color: ${selectedColor}`">
                        <span x-text="categoryName.charAt(0).toUpperCase() || '?'"></span>
                    </div>
                    <div>
                        <h4 class="text-base font-medium text-gray-900" x-text="categoryName || 'Nome da categoria'"></h4>
                        <p class="text-sm text-gray-500">{{ $category->expenses_count }} despesas</p>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-between pt-5 border-t border-gray-200">
                <a href="{{ route('categories.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Atualizar Categoria
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function categoryForm() {
    return {
        selectedColor: '{{ old('color', $category->color) }}',
        categoryName: '{{ old('name', $category->name) }}',
        suggestedColors: [
            '#6366f1', // Indigo
            '#8b5cf6', // Violet
            '#ec4899', // Pink
            '#ef4444', // Red
            '#f97316', // Orange
            '#f59e0b', // Amber
            '#eab308', // Yellow
            '#84cc16', // Lime
            '#22c55e', // Green
            '#10b981', // Emerald
            '#14b8a6', // Teal
            '#06b6d4', // Cyan
            '#0ea5e9', // Sky
            '#3b82f6', // Blue
            '#6366f1', // Indigo
            '#8b5cf6', // Violet
        ],
        
        validateColor() {
            // Remove espaços e garante o #
            this.selectedColor = this.selectedColor.trim();
            if (!this.selectedColor.startsWith('#')) {
                this.selectedColor = '#' + this.selectedColor;
            }
            // Garante uppercase
            this.selectedColor = this.selectedColor.toUpperCase();
        },
        
        init() {
            // Observar mudanças no nome
            this.$watch('categoryName', () => {
                const input = document.getElementById('name');
                if (input) {
                    this.categoryName = input.value;
                }
            });
            
            // Atualizar ao digitar no campo nome
            const nameInput = document.getElementById('name');
            if (nameInput) {
                nameInput.addEventListener('input', (e) => {
                    this.categoryName = e.target.value;
                });
            }
        }
    }
}
</script>
@endpush