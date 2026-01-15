<!-- Filtros -->
<div class="bg-white shadow rounded-lg p-6 mb-6">
    <form method="GET" action="{{ route('leads.index') }}" class="space-y-4">
        <input type="hidden" name="view" value="list">
        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <!-- Busca -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700">Buscar</label>
                <input type="text" 
                       name="search" 
                       id="search" 
                       value="{{ request('search') }}" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       placeholder="Título, contato, empresa ou email...">
            </div>

            <!-- Pipeline -->
            <div>
                <label for="pipeline_id" class="block text-sm font-medium text-gray-700">Pipeline</label>
                <select name="pipeline_id" id="pipeline_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Todos</option>
                    @foreach($pipelines as $pipeline)
                        <option value="{{ $pipeline->id }}" {{ request('pipeline_id') == $pipeline->id ? 'selected' : '' }}>
                            {{ $pipeline->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Todos</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Aberto</option>
                    <option value="won" {{ request('status') == 'won' ? 'selected' : '' }}>Ganho</option>
                    <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Perdido</option>
                </select>
            </div>

            <!-- Responsável -->
            <div>
                <label for="assigned_to" class="block text-sm font-medium text-gray-700">Responsável</label>
                <select name="assigned_to" id="assigned_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Todos</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Filtrar
                </button>
                <a href="{{ route('leads.index', ['view' => 'list']) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    Limpar
                </a>
            </div>

            <div class="text-sm text-gray-500 hidden sm:block">
                Mostrando {{ $leads->firstItem() ?? 0 }} - {{ $leads->lastItem() ?? 0 }} de {{ $leads->total() }} leads
            </div>
        </div>
    </form>
</div>

<!-- Tabela de Leads -->
<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lead</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estágio</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsável</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($leads as $lead)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-2 h-10 rounded mr-3" style="background-color: {{ $lead->stage->color }}"></div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $lead->title }}</div>
                                    @if($lead->company_name)
                                        <div class="text-sm text-gray-500">{{ $lead->company_name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $lead->contact_name }}</div>
                            @if($lead->contact_email)
                                <div class="text-sm text-gray-500">{{ $lead->contact_email }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: {{ $lead->stage->color }}20; color: {{ $lead->stage->color }}">
                                {{ $lead->stage->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($lead->value > 0)
                                <div class="text-sm font-semibold text-gray-900">R$ {{ number_format($lead->value, 2, ',', '.') }}</div>
                            @else
                                <div class="text-sm text-gray-400">-</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($lead->assignedTo)
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center">
                                            <span class="text-xs font-medium text-white">{{ substr($lead->assignedTo->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $lead->assignedTo->name }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($lead->status == 'won') bg-green-100 text-green-800
                                @elseif($lead->status == 'open') bg-blue-100 text-blue-800
                                @elseif($lead->status == 'lost') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                @if($lead->status == 'won') Ganho
                                @elseif($lead->status == 'open') Aberto
                                @elseif($lead->status == 'lost') Perdido
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('leads.show', $lead) }}" class="text-indigo-600 hover:text-indigo-900" title="Ver">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>

                                <a href="{{ route('leads.edit', $lead) }}" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>

                                <form method="POST" action="{{ route('leads.destroy', $lead) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este lead?')">
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
                            <p class="mt-2">Nenhum lead encontrado</p>
                            <button @click="$dispatch('open-modal')" class="mt-4 inline-flex items-center text-indigo-600 hover:text-indigo-500">
                                Criar seu primeiro lead
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if($leads->hasPages())
        <div class="px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            {{ $leads->appends(['view' => 'list'])->links() }}
        </div>
    @endif
</div>
