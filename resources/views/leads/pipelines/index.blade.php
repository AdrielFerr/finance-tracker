@extends('layouts.app')

@section('title', 'Pipelines')
@section('page-title', 'Gerenciar Pipelines')

@section('content')
<div class="space-y-6" x-data="{ modalOpen: false, editModalOpen: false, currentPipeline: null }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Pipelines</h2>
            <p class="mt-1 text-sm text-gray-500 hidden sm:block">Configure os funis de vendas e seus estágios</p>
        </div>
        <div class="flex items-center space-x-2 sm:space-x-3">
            <a href="{{ route('leads.index') }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="hidden sm:inline">Voltar para Leads</span>
            </a>
            
            <button @click="modalOpen = true" class="inline-flex items-center px-3 sm:px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="hidden sm:inline">Novo Pipeline</span>
                <span class="sm:hidden">+</span>
            </button>
        </div>
    </div>

    <!-- Lista de Pipelines -->
    <div class="grid grid-cols-1 gap-6">
        @forelse($pipelines as $pipeline)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <!-- Header do Pipeline -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <h3 class="text-lg font-medium text-gray-900">{{ $pipeline->name }}</h3>
                            @if($pipeline->is_default)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    Padrão
                                </span>
                            @endif
                            @if(!$pipeline->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inativo
                                </span>
                            @endif
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('leads.pipelines.edit', $pipeline) }}" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            
                            @if(!$pipeline->is_default)
                                <form method="POST" action="{{ route('leads.pipelines.destroy', $pipeline) }}" class="inline" onsubmit="return confirm('Tem certeza? Todos os leads deste pipeline serão movidos para o pipeline padrão.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Excluir">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    
                    @if($pipeline->description)
                        <p class="mt-1 text-sm text-gray-500">{{ $pipeline->description }}</p>
                    @endif
                </div>

                <!-- Estágios -->
                <div class="px-6 py-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Estágios ({{ $pipeline->stages->count() }})</h4>
                    <div class="flex flex-wrap gap-2">
                        @forelse($pipeline->stages->sortBy('order') as $stage)
                            <div class="inline-flex items-center px-3 py-2 rounded-lg border-2 text-sm font-medium" 
                                 style="border-color: {{ $stage->color }}; background-color: {{ $stage->color }}20;">
                                <span style="color: {{ $stage->color }}">{{ $stage->name }}</span>
                                <span class="ml-2 text-xs text-gray-500">({{ $stage->order }})</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Nenhum estágio configurado</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white shadow rounded-lg p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                <p class="mt-2 text-gray-500">Nenhum pipeline encontrado</p>
                <button @click="modalOpen = true" class="mt-4 inline-flex items-center text-indigo-600 hover:text-indigo-500">
                    Criar seu primeiro pipeline
                </button>
            </div>
        @endforelse
    </div>

    <!-- Modal Criar Pipeline -->
    <div x-show="modalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Overlay -->
        <div x-show="modalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
             @click="modalOpen = false"></div>

        <!-- Modal Content -->
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="modalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                
                <!-- Header Modal -->
                <div class="bg-white px-6 pt-5 pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Novo Pipeline</h3>
                        <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Formulário -->
                    <form method="POST" action="{{ route('leads.pipelines.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Nome do Pipeline <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                   placeholder="Ex: Vendas B2B">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="description" id="description" rows="2"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                      placeholder="Descrição opcional do pipeline"></textarea>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1" checked
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Pipeline ativo
                            </label>
                        </div>

                        <!-- Botões -->
                        <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                            <button type="button" @click="modalOpen = false" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Criar Pipeline
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection