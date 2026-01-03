@extends('layouts.app')

@section('title', 'Meu Perfil')
@section('page-title', 'Perfil')

@section('content')
<div class="max-w-7xl mx-auto" x-data="profileManager()">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Meu Perfil</h2>
        <p class="mt-1 text-sm text-gray-500">Gerencie suas informações pessoais e configurações</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar com Avatar e Stats -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Card de Avatar -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex flex-col items-center">
                    <!-- Avatar -->
                    <div class="relative">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" 
                                 alt="{{ $user->name }}" 
                                 class="w-32 h-32 rounded-full object-cover border-4 border-indigo-100">
                        @else
                            <div class="w-32 h-32 rounded-full bg-indigo-100 flex items-center justify-center border-4 border-indigo-200">
                                <span class="text-4xl font-bold text-indigo-600">
                                    {{ substr($user->name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                        
                        <!-- Botão de Upload -->
                        <button @click="$refs.avatarInput.click()" 
                                class="absolute bottom-0 right-0 bg-indigo-600 text-white p-2 rounded-full hover:bg-indigo-700 shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Form de Upload (oculto) -->
                    <form action="{{ route('profile.update-avatar') }}" method="POST" enctype="multipart/form-data" class="hidden">
                        @csrf
                        <input type="file" 
                               x-ref="avatarInput" 
                               name="avatar" 
                               accept="image/*"
                               @change="$el.form.submit()">
                    </form>

                    <!-- Nome e Email -->
                    <h3 class="mt-4 text-xl font-bold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    <p class="mt-1 text-xs text-gray-400">Membro desde {{ $stats['member_since'] }}</p>

                    <!-- Remover Avatar -->
                    @if($user->avatar)
                        <form action="{{ route('profile.remove-avatar') }}" method="POST" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:text-red-800">
                                Remover foto
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Estatísticas</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Despesas</span>
                        <span class="text-lg font-semibold text-gray-900">{{ $stats['total_expenses'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Categorias</span>
                        <span class="text-lg font-semibold text-gray-900">{{ $stats['total_categories'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Métodos</span>
                        <span class="text-lg font-semibold text-gray-900">{{ $stats['total_payment_methods'] }}</span>
                    </div>
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Total Gasto</span>
                            <span class="text-lg font-semibold text-indigo-600">
                                R$ {{ number_format($stats['total_spent'], 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo Principal com Tabs -->
        <div class="lg:col-span-2">
            <!-- Tabs -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button @click="activeTab = 'info'"
                                :class="activeTab === 'info' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm">
                            Informações
                        </button>
                        <button @click="activeTab = 'security'"
                                :class="activeTab === 'security' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm">
                            Segurança
                        </button>
                        <button @click="activeTab = 'danger'"
                                :class="activeTab === 'danger' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm">
                            Zona Perigosa
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <!-- Tab: Informações -->
                    <div x-show="activeTab === 'info'" x-cloak>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informações Pessoais</h3>
                        <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nome Completo</label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       value="{{ old('name', $user->name) }}"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-300 @enderror">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       value="{{ old('email', $user->email) }}"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('email') border-red-300 @enderror">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-700">
                                    Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab: Segurança -->
                    <div x-show="activeTab === 'security'" x-cloak>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Alterar Senha</h3>
                        <form action="{{ route('profile.update-password') }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700">Senha Atual</label>
                                <input type="password" 
                                       name="current_password" 
                                       id="current_password" 
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('current_password') border-red-300 @enderror">
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Nova Senha</label>
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('password') border-red-300 @enderror">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Nova Senha</label>
                                <input type="password" 
                                       name="password_confirmation" 
                                       id="password_confirmation" 
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-700">
                                    Alterar Senha
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab: Zona Perigosa -->
                    <div x-show="activeTab === 'danger'" x-cloak>
                        <h3 class="text-lg font-medium text-red-900 mb-2">Zona Perigosa</h3>
                        <p class="text-sm text-gray-600 mb-6">Ações irreversíveis que afetam sua conta permanentemente.</p>
                        
                        <div class="border border-red-200 rounded-lg p-6 bg-red-50">
                            <h4 class="text-base font-medium text-red-900 mb-2">Excluir Conta</h4>
                            <p class="text-sm text-red-700 mb-4">
                                Ao excluir sua conta, todos os seus dados serão permanentemente removidos, incluindo:
                            </p>
                            <ul class="list-disc list-inside text-sm text-red-700 mb-4 space-y-1">
                                <li>Todas as suas despesas</li>
                                <li>Categorias personalizadas</li>
                                <li>Métodos de pagamento</li>
                                <li>Histórico e relatórios</li>
                            </ul>
                            <p class="text-sm font-semibold text-red-800 mb-4">Esta ação não pode ser desfeita!</p>
                            
                            <button @click="showDeleteModal = true" 
                                    class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-700">
                                Excluir Minha Conta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div x-show="showDeleteModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @click.self="showDeleteModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmar Exclusão da Conta</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Para confirmar a exclusão da sua conta, digite sua senha:
                </p>
                
                <form action="{{ route('profile.destroy') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    
                    <div class="mb-4">
                        <input type="password" 
                               name="password" 
                               required
                               placeholder="Digite sua senha"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                                @click="showDeleteModal = false"
                                class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-semibold rounded-md hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-700">
                            Sim, Excluir Minha Conta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection

@push('scripts')
<script>
function profileManager() {
    return {
        activeTab: 'info',
        showDeleteModal: false
    }
}
</script>
@endpush