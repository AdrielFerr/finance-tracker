<div x-data="{ open: false }" class="relative" @click.outside="open = false">
    <button type="button" @click="open = !open"
            class="relative -m-2.5 p-2.5 text-gray-400 hover:text-gray-500">
        <span class="sr-only">Ver notificações</span>
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>

        @if($bellUnreadCount > 0)
            <span class="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white">
                {{ $bellUnreadCount > 9 ? '9+' : $bellUnreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak
         class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">

        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
            <p class="text-sm font-semibold text-gray-900">Notificações</p>

            @if($bellUnreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                        Marcar todas como lidas
                    </button>
                </form>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse($bellNotifications as $notification)
                <a href="{{ route('notifications.open', $notification) }}"
                   class="flex gap-3 px-4 py-3 border-b border-gray-100 hover:bg-gray-50 {{ $notification->isUnread() ? 'bg-indigo-50/50' : '' }}">

                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full
                        @if($notification->level === 'danger') bg-red-500
                        @elseif($notification->level === 'warning') bg-amber-500
                        @else bg-indigo-500 @endif
                        {{ $notification->isUnread() ? '' : 'opacity-30' }}"></span>

                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-medium text-gray-900 truncate">
                            {{ $notification->title }}
                        </span>
                        <span class="block text-xs text-gray-600 mt-0.5">
                            {{ $notification->body }}
                        </span>
                        <span class="block text-[11px] text-gray-400 mt-1">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </span>
                </a>
            @empty
                <p class="px-4 py-8 text-center text-sm text-gray-500">
                    Nenhuma notificação por enquanto.
                </p>
            @endforelse
        </div>
    </div>
</div>
