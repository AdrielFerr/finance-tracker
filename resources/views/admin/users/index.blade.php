@extends('layouts.app')

@section('title', 'Gerenciar Usuários')
@section('page-title', 'Gerenciar Usuários')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Gerenciar Usuários</h2>
            <p class="mt-1 text-sm text-gray-500">Gerencie todos os usuários do sistema</p>
        </div>
        
        <button @click="$dispatch('open-user-modal')"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Novo Usuário
        </button>
    </div>

    <!-- Tabela de Usuários -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuário</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Empresa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Criado em</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-indigo-600 font-semibold">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->tenant)
                                <span class="text-sm text-gray-900">{{ $user->tenant->name }}</span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                @if($user->role == 'super_admin') bg-purple-100 text-purple-800
                                @elseif($user->role == 'tenant_admin') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <button @click="$dispatch('open-edit-user-modal', {
                                            id: {{ $user->id }},
                                            name: '{{ addslashes($user->name) }}',
                                            email: '{{ $user->email }}',
                                            tenant_id: {{ $user->tenant_id ?? 'null' }},
                                            role: '{{ $user->role }}',
                                            is_active: {{ $user->is_active ? 'true' : 'false' }}
                                        })"
                                        class="text-indigo-600 hover:text-indigo-900" 
                                        title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>

                                @if($user->role !== 'super_admin' && $user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="{{ $user->is_active ? 'text-orange-600 hover:text-orange-900' : 'text-green-600 hover:text-green-900' }}" 
                                                title="{{ $user->is_active ? 'Desativar' : 'Ativar' }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($user->is_active)
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                @endif
                                            </svg>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900" 
                                                title="Excluir"
                                                onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <p class="mt-2">Nenhum usuário cadastrado</p>
                            <button @click="$dispatch('open-user-modal')" 
                                    class="mt-4 inline-flex items-center text-indigo-600 hover:text-indigo-500">
                                Criar primeiro usuário
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if($users->hasPages())
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg shadow">
            {{ $users->links() }}
        </div>
    @endif
</div>

<!-- Include dos Modals -->
@include('admin.users.partials.create-modal')
@include('admin.users.partials.edit-modal')
@endsection