<div x-data="kanbanBoard()" x-init="init()">

    <!-- Seletor de Pipeline -->
    <div class="mb-6 flex items-center justify-between bg-white shadow rounded-xl p-4">
        <div class="flex items-center space-x-3">
            <label for="kanban_pipeline" class="text-sm font-medium text-gray-700">
                Pipeline:
            </label>
            <select 
                id="kanban_pipeline"
                onchange="window.location.href='{{ route('leads.index') }}?view=kanban&pipeline_id='+this.value"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @foreach($pipelines as $pipeline)
                    <option value="{{ $pipeline->id }}"
                        {{ request('pipeline_id', $selectedPipeline->id) == $pipeline->id ? 'selected' : '' }}>
                        {{ $pipeline->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="text-sm text-gray-500 font-medium">
            {{ $leadsByStage->flatten()->count() }} leads no total
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="overflow-x-auto pb-4 custom-scrollbar">
        <div class="flex gap-4 w-full min-w-max lg:min-w-full">

            @foreach($selectedPipeline->stages->sortBy('order') as $stage)

                @php
                    $stageLeads = $leadsByStage->get($stage->id, collect());
                    $stageValue = $stageLeads->sum('value');
                @endphp

                <div class="flex-1 min-w-[300px] max-w-[450px] flex flex-col">

                    <div class="bg-white rounded-xl shadow-sm p-4 mb-3 border-t-4 transition hover:shadow-md"
                         style="border-color: {{ $stage->color }}">

                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full ring-2 ring-white"
                                     style="background-color: {{ $stage->color }}"></div>
                                <h3 class="font-semibold text-gray-800 text-sm tracking-wide">
                                    {{ $stage->name }}
                                </h3>
                            </div>

                            <span class="px-2 py-0.5 text-[11px] font-semibold text-gray-700 bg-gray-100 rounded-full">
                                {{ $stageLeads->count() }}
                            </span>
                        </div>

                        @if($stageValue > 0)
                            <div class="mt-1 text-xs font-semibold text-emerald-600 flex items-center gap-1">
                                💰 R$ {{ number_format($stageValue, 2, ',', '.') }}
                            </div>
                        @endif
                    </div>

                    <div 
                        class="kanban-column space-y-3 min-h-[450px] bg-gray-50/80 backdrop-blur rounded-xl p-3 border border-gray-200 h-full"
                        data-stage-id="{{ $stage->id }}">

                        @forelse($stageLeads as $lead)

                            <div 
                                class="kanban-card bg-white rounded-xl shadow-sm p-4 cursor-move 
                                       hover:shadow-lg hover:-translate-y-0.5 transition-all 
                                       border border-gray-200"
                                data-lead-id="{{ $lead->id }}">

                                <div class="flex items-start justify-between mb-2 gap-2">
                                    <h4 class="text-sm font-semibold text-gray-900 leading-snug uppercase">
                                        {{ $lead->title }}
                                    </h4>

                                    <button 
                                        onclick="window.location.href='{{ route('leads.show', $lead) }}'"
                                        class="text-gray-400 hover:text-indigo-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>

                                @if($lead->company_name)
                                    <div class="text-xs text-gray-500 mb-2 flex items-center gap-1 truncate font-medium">
                                        🏢 {{ $lead->company_name }}
                                    </div>
                                @endif

                                <div class="text-xs text-gray-600 mb-3 leading-tight border-l-2 border-gray-100 pl-2">
                                    <span class="block font-medium">👤 {{ $lead->contact_name }}</span>
                                    @if($lead->contact_email)
                                        <div class="text-gray-400 truncate text-[11px]">
                                            {{ $lead->contact_email }}
                                        </div>
                                    @endif
                                </div>

                                @if($lead->value > 0)
                                    <div class="text-sm font-bold text-emerald-600 mb-3 flex items-center gap-1">
                                        💵 R$ {{ number_format($lead->value, 2, ',', '.') }}
                                    </div>
                                @endif

                                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                    @if($lead->assignedTo)
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center shadow">
                                                <span class="text-[10px] font-bold text-white uppercase">
                                                    {{ substr($lead->assignedTo->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <span class="text-[11px] text-gray-700 font-bold uppercase">
                                                {{ explode(' ', $lead->assignedTo->name)[0] }}
                                            </span>
                                        </div>
                                    @else
                                        <div></div>
                                    @endif

                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md
                                        @if($lead->status == 'won') bg-green-50 text-green-700 border border-green-200
                                        @elseif($lead->status == 'open') bg-blue-50 text-blue-700 border border-blue-200
                                        @elseif($lead->status == 'lost') bg-red-50 text-red-700 border border-red-200
                                        @endif">
                                        {{ $lead->status == 'won' ? 'Ganho' : ($lead->status == 'open' ? 'Aberto' : 'Perdido') }}
                                    </span>
                                </div>
                            </div>

                        @empty
                            <div class="flex flex-col items-center justify-center py-12 opacity-40">
                                <div class="w-12 h-12 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center mb-2">
                                    <span class="text-gray-400 text-xs">0</span>
                                </div>
                                <p class="text-[11px] font-bold uppercase tracking-tighter text-gray-400">Vazio</p>
                            </div>
                        @endforelse

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
                    group: 'kanban-leads',
                    animation: 200,
                    ghostClass: 'opacity-30',
                    dragClass: 'shadow-2xl scale-105',
                    handle: '.kanban-card',
                    
                    onEnd: async (evt) => {
                        const leadId = evt.item.dataset.leadId;
                        const newStageId = evt.to.dataset.stageId;
                        const newOrder = evt.newIndex;
                        
                        await this.moveCard(leadId, newStageId, newOrder);
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
                
                if (data.success) {
                    // Atualizar página para refletir mudanças
                    window.location.reload();
                } else {
                    alert('Erro ao mover lead');
                    window.location.reload();
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao mover lead');
                window.location.reload();
            }
        }
    }
}
</script>
@endpush
