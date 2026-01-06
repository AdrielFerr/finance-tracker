@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $tenant->name }}</h2>
            <p class="text-sm text-gray-500">{{ $tenant->email }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.tenants.edit', $tenant) }}" 
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Editar
            </a>
            <a href="{{ route('admin.tenants.index') }}" 
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                Voltar
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-sm text-gray-500">Usuários</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-sm text-gray-500">Despesas</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_expenses'] }}</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-sm text-gray-500">Categorias</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_categories'] }}</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-sm text-gray-500">Métodos</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_payment_methods'] }}</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-sm text-gray-500">Total Gasto</div>
            <div class="text-2xl font-bold text-green-600">R$ {{ number_format($stats['total_spent'], 2, ',', '.') }}</div>
        </div>
    </div>

    <!-- Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Informações</h3>
            <dl class="space-y-2">
                <div><dt class="text-sm text-gray-500">Telefone:</dt><dd>{{ $tenant->phone ?? 'N/A' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Endereço:</dt><dd>{{ $tenant->address ?? 'N/A' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Plano:</dt><dd class="uppercase">{{ $tenant->plan }}</dd></div>
                <div><dt class="text-sm text-gray-500">Status:</dt><dd>{{ $tenant->status }}</dd></div>
            </dl>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Limites</h3>
            <dl class="space-y-2">
                <div><dt class="text-sm text-gray-500">Máx. Usuários:</dt><dd>{{ $tenant->max_users }}</dd></div>
                <div><dt class="text-sm text-gray-500">Máx. Despesas:</dt><dd>{{ $tenant->max_expenses }}</dd></div>
                <div><dt class="text-sm text-gray-500">Criado em:</dt><dd>{{ $tenant->created_at->format('d/m/Y H:i') }}</dd></div>
            </dl>
        </div>
    </div>

    <!-- Usuários -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Usuários</h3>
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="text-left text-xs text-gray-500 uppercase">Nome</th>
                    <th class="text-left text-xs text-gray-500 uppercase">Email</th>
                    <th class="text-left text-xs text-gray-500 uppercase">Role</th>
                    <th class="text-left text-xs text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tenant->users as $user)
                <tr>
                    <td class="py-2">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">{{ $user->role }}</span></td>
                    <td><span class="px-2 py-1 text-xs {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} rounded">{{ $user->is_active ? 'Ativo' : 'Inativo' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection