@extends('layouts.app')

@section('title', 'Editar Pipeline')
@section('page-title', 'Editar Pipeline')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Editar Pipeline</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $pipeline->name }}</p>
        </div>
        <a href="{{ route('leads.pipelines.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar
        </a>
    </div>

    <!-- Formulário Pipeline -->
    <form method="POST" action="{{ route('leads.pipelines.update', $pipeline) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informações do Pipeline</h3>
            
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Nome do Pipeline <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $pipeline->name) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-300 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('description') border-red-300 @enderror">{{ old('description', $pipeline->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center space-x-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $pipeline->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            Pipeline ativo
                        </label>
                    </div>

                    @if(!$pipeline->is_default)
                        <div class="flex items-center">
                            <input type="checkbox" name="is_default" id="is_default" value="1" {{ old('is_default', $pipeline->is_default) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="is_default" class="ml-2 block text-sm text-gray-700">
                                Definir como padrão
                            </label>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 mt-6">
                <a href="{{ route('leads.pipelines.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Salvar Alterações
                </button>
            </div>
        </div>
    </form>

    <!-- Gerenciar Estágios -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Estágios do Pipeline</h3>
            <button onclick="openStageModal()" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Adicionar Estágio
            </button>
        </div>

        <div class="space-y-3">
            @forelse($pipeline->stages->sortBy('order') as $stage)
                <div class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                    <div class="flex items-center space-x-4">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium text-gray-600 bg-gray-100">
                            {{ $stage->order }}
                        </div>
                        <div class="w-4 h-4 rounded" style="background-color: {{ $stage->color }}"></div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $stage->name }}</div>
                            <div class="text-xs text-gray-500">Probabilidade: {{ $stage->win_probability }}%</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <button onclick="editStage({{ $stage->id }}, '{{ $stage->name }}', '{{ $stage->color }}', {{ $stage->win_probability }}, {{ $stage->order }})" class="text-indigo-600 hover:text-indigo-900">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        
                        <form method="POST" action="{{ route('leads.pipelines.stages.destroy', $stage) }}" class="inline" onsubmit="return confirm('Tem certeza? Leads neste estágio serão movidos para o primeiro estágio.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 py-8">Nenhum estágio configurado. Adicione o primeiro estágio.</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Adicionar/Editar Estágio -->
<div id="stageModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="closeStageModal()"></div>
    
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="stageModalTitle">Novo Estágio</h3>
                    <button onclick="closeStageModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form id="stageForm" method="POST" action="{{ route('leads.pipelines.stages.store', $pipeline) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" id="stageMethod" name="_method" value="POST">
                    <input type="hidden" name="pipeline_id" value="{{ $pipeline->id }}">

                    <div>
                        <label for="stage_name" class="block text-sm font-medium text-gray-700">
                            Nome do Estágio <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="stage_name" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="stage_color" class="block text-sm font-medium text-gray-700">Cor</label>
                        <input type="color" name="color" id="stage_color" value="#6366f1"
                               class="mt-1 block w-20 h-10 rounded-md border-gray-300">
                    </div>

                    <div>
                        <label for="stage_win_probability" class="block text-sm font-medium text-gray-700">Probabilidade de Ganho (%)</label>
                        <input type="number" name="win_probability" id="stage_win_probability" min="0" max="100" value="50"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="stage_order" class="block text-sm font-medium text-gray-700">Ordem</label>
                        <input type="number" name="order" id="stage_order" min="1" value="{{ $pipeline->stages->count() + 1 }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                        <button type="button" onclick="closeStageModal()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Salvar Estágio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openStageModal() {
    document.getElementById('stageModal').classList.remove('hidden');
    document.getElementById('stageModalTitle').textContent = 'Novo Estágio';
    document.getElementById('stageForm').action = '{{ route('leads.pipelines.stages.store', $pipeline) }}';
    document.getElementById('stageMethod').value = 'POST';
    document.getElementById('stage_name').value = '';
    document.getElementById('stage_color').value = '#6366f1';
    document.getElementById('stage_win_probability').value = '50';
    document.getElementById('stage_order').value = '{{ $pipeline->stages->count() + 1 }}';
}

function editStage(id, name, color, probability, order) {
    document.getElementById('stageModal').classList.remove('hidden');
    document.getElementById('stageModalTitle').textContent = 'Editar Estágio';
    document.getElementById('stageForm').action = '/leads/pipelines/stages/' + id;
    document.getElementById('stageMethod').value = 'PUT';
    document.getElementById('stage_name').value = name;
    document.getElementById('stage_color').value = color;
    document.getElementById('stage_win_probability').value = probability;
    document.getElementById('stage_order').value = order;
}

function closeStageModal() {
    document.getElementById('stageModal').classList.add('hidden');
}

// Fechar modal com ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeStageModal();
    }
});
</script>
@endsection
