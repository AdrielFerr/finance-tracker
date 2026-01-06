@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h2 class="text-2xl font-bold">Dashboard Super Admin</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-sm text-gray-500">Total Empresas</div>
            <div class="text-3xl font-bold text-indigo-600">{{ \App\Models\Tenant::count() }}</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-sm text-gray-500">Empresas Ativas</div>
            <div class="text-3xl font-bold text-green-600">{{ \App\Models\Tenant::where('status', 'active')->count() }}</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-sm text-gray-500">Total Usuários</div>
            <div class="text-3xl font-bold text-blue-600">{{ \App\Models\User::count() }}</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-sm text-gray-500">Total Despesas</div>
            <div class="text-3xl font-bold text-purple-600">{{ \App\Models\Expense::count() }}</div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Últimas Empresas Criadas</h3>
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="text-left text-xs text-gray-500 uppercase">Nome</th>
                    <th class="text-left text-xs text-gray-500 uppercase">Plano</th>
                    <th class="text-left text-xs text-gray-500 uppercase">Status</th>
                    <th class="text-left text-xs text-gray-500 uppercase">Criado em</th>
                </tr>
            </thead>
            <tbody>
                @foreach(\App\Models\Tenant::latest()->take(5)->get() as $tenant)
                <tr>
                    <td class="py-2">{{ $tenant->name }}</td>
                    <td><span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">{{ $tenant->plan }}</span></td>
                    <td><span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">{{ $tenant->status }}</span></td>
                    <td>{{ $tenant->created_at->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection