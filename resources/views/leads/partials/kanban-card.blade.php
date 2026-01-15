{{-- resources/views/leads/partials/kanban-card.blade.php --}}

<div 
    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition cursor-move p-4 border-l-4"
    style="border-color: {{ $lead->stage->color }}"
    data-lead-id="{{ $lead->id }}"
>
    {{-- Header --}}
    <div class="flex items-start justify-between mb-2">
        <a 
            href="{{ route('leads.show', $lead->id) }}"
            class="font-semibold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 flex-1"
        >
            {{ $lead->title }}
        </a>
        
        {{-- Priority Badge --}}
        @if($lead->priority !== 'medium')
            <span class="ml-2 px-2 py-1 text-xs rounded-full
                {{ $lead->priority === 'urgent' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                {{ $lead->priority === 'high' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : '' }}
                {{ $lead->priority === 'low' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}
            ">
                @if($lead->priority === 'urgent') 🔥 
                @elseif($lead->priority === 'high') ⚠️ 
                @endif
                {{ ucfirst($lead->priority) }}
            </span>
        @endif
    </div>

    {{-- Company/Contact --}}
    <div class="text-sm text-gray-600 dark:text-gray-400 mb-3">
        <div class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>{{ $lead->contact_name }}</span>
        </div>
        
        @if($lead->company_name)
            <div class="flex items-center gap-1 mt-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>{{ $lead->company_name }}</span>
            </div>
        @endif
    </div>

    {{-- Value --}}
    @if($lead->value > 0)
        <div class="text-lg font-bold text-green-600 dark:text-green-400 mb-3">
            R$ {{ number_format($lead->value, 2, ',', '.') }}
        </div>
    @endif

    {{-- Footer --}}
    <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
        {{-- Assigned User --}}
        @if($lead->assignedTo)
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-semibold">
                    {{ substr($lead->assignedTo->name, 0, 1) }}
                </div>
                <span class="text-xs text-gray-600 dark:text-gray-400">
                    {{ explode(' ', $lead->assignedTo->name)[0] }}
                </span>
            </div>
        @else
            <div class="text-xs text-gray-400">Não atribuído</div>
        @endif

        {{-- Expected Close Date --}}
        @if($lead->expected_close_date)
            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ $lead->expected_close_date->format('d/m/y') }}
            </div>
        @endif
    </div>

    {{-- Activities Count --}}
    @if($lead->activities->count() > 0)
        <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    {{ $lead->activities->count() }}
                </span>
                
                @if($lead->products->count() > 0)
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        {{ $lead->products->count() }}
                    </span>
                @endif
            </div>
        </div>
    @endif
</div>
