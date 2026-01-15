@extends('layouts.app')

@section('title', 'Kanban de Leads')

@section('content')
<div class="container mx-auto px-4 py-6">
    
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Kanban de Leads</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $pipeline->name }}</p>
        </div>
        
        <div class="flex items-center gap-3">
            {{-- Seletor de Pipeline --}}
            <select 
                onchange="window.location.href='{{ route('leads.kanban') }}?pipeline_id='+this.value"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
            >
                @foreach($pipelines as $p)
                    <option value="{{ $p->id }}" {{ $p->id == $pipeline->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
            
            {{-- Botão Novo Lead --}}
            <a 
                href="{{ route('leads.create', ['pipeline_id' => $pipeline->id]) }}"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg flex items-center gap-2 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Novo Lead
            </a>
        </div>
    </div>

    {{-- Estatísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Total de Leads</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_leads'] }}</div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Valor Total</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                R$ {{ number_format($stats['total_value'], 2, ',', '.') }}
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Taxa de Conversão</div>
            <div class="text-2xl font-bold text-green-600 mt-1">{{ number_format($stats['conversion_rate'], 1) }}%</div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Leads Abertos</div>
            <div class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['open_leads'] }}</div>
        </div>
    </div>

    {{-- Kanban Board --}}
    <div 
        x-data="kanbanBoard()" 
        class="overflow-x-auto pb-4"
    >
        <div class="flex gap-4 min-w-max">
            @foreach($pipeline->stages as $stage)
                <div class="flex-shrink-0 w-80">
                    {{-- Stage Header --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div 
                                    class="w-3 h-3 rounded-full" 
                                    style="background-color: {{ $stage->color }}"
                                ></div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">
                                    {{ $stage->name }}
                                </h3>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $leadsByStage->get($stage->id)?->count() ?? 0 }}
                            </span>
                        </div>
                        
                        {{-- Valor total do estágio --}}
                        @php
                            $stageValue = $leadsByStage->get($stage->id)?->sum('value') ?? 0;
                        @endphp
                        @if($stageValue > 0)
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                R$ {{ number_format($stageValue, 2, ',', '.') }}
                            </div>
                        @endif
                    </div>

                    {{-- Cards Container --}}
                    <div 
                        class="kanban-column space-y-3 min-h-[200px] bg-gray-50 dark:bg-gray-900 rounded-lg p-3"
                        data-stage-id="{{ $stage->id }}"
                    >
                        @foreach($leadsByStage->get($stage->id, collect()) as $lead)
                            @include('leads.partials.kanban-card', ['lead' => $lead])
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function kanbanBoard() {
    return {
        init() {
            const columns = document.querySelectorAll('.kanban-column');
            
            columns.forEach(column => {
                new Sortable(column, {
                    group: 'leads',
                    animation: 150,
                    ghostClass: 'opacity-50',
                    dragClass: 'shadow-2xl',
                    
                    onEnd: (evt) => {
                        const leadId = evt.item.dataset.leadId;
                        const newStageId = evt.to.dataset.stageId;
                        const newOrder = evt.newIndex;
                        
                        this.moveCard(leadId, newStageId, newOrder);
                    }
                });
            });
        },
        
        async moveCard(leadId, stageId, order) {
            try {
                const response = await fetch(`/leads/${leadId}/move-stage`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        stage_id: stageId,
                        order: order
                    })
                });
                
                const data = await response.json();
                
                if (!data.success) {
                    alert('Erro ao mover lead');
                    location.reload();
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao mover lead');
                location.reload();
            }
        }
    }
}
</script>
@endpush
@endsection
