@extends('layouts.app')

@section('title', 'Editar Método de Pagamento')
@section('page-title', 'Editar Método')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('payment-methods.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Voltar para métodos
        </a>
    </div>

    <div class="bg-white shadow rounded-lg" x-data="paymentMethodForm()">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Editar Método de Pagamento</h3>
            <p class="mt-1 text-sm text-gray-500">Atualize as informações do método</p>
        </div>

        <form action="{{ route('payment-methods.update', $paymentMethod) }}" method="POST" class="px-6 py-5 space-y-6">
            @csrf
            @method('PUT')

            <!-- Tipo -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                    Tipo de Método <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <template x-for="(label, value) in typeOptions" :key="value">
                        <label class="relative flex flex-col items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                               :class="selectedType === value ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" 
                                   name="type" 
                                   :value="value" 
                                   x-model="selectedType"
                                   class="sr-only">
                            <span class="text-2xl mb-2" x-text="getTypeIcon(value)"></span>
                            <span class="text-xs font-medium text-center" x-text="label"></span>
                        </label>
                    </template>
                </div>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nome -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Nome <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $paymentMethod->name) }}"
                       required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-300 @enderror"
                       placeholder="Ex: Nubank, Inter, Dinheiro...">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Campos específicos para Cartão de Crédito/Débito -->
            <div x-show="selectedType === 'credit_card' || selectedType === 'debit_card'" class="space-y-6" x-cloak>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Bandeira -->
                    <div>
                        <label for="brand" class="block text-sm font-medium text-gray-700">Bandeira</label>
                        <select name="brand" id="brand" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Selecione...</option>
                            <option value="Visa" {{ old('brand', $paymentMethod->brand) == 'Visa' ? 'selected' : '' }}>Visa</option>
                            <option value="Mastercard" {{ old('brand', $paymentMethod->brand) == 'Mastercard' ? 'selected' : '' }}>Mastercard</option>
                            <option value="Elo" {{ old('brand', $paymentMethod->brand) == 'Elo' ? 'selected' : '' }}>Elo</option>
                            <option value="American Express" {{ old('brand', $paymentMethod->brand) == 'American Express' ? 'selected' : '' }}>American Express</option>
                            <option value="Hipercard" {{ old('brand', $paymentMethod->brand) == 'Hipercard' ? 'selected' : '' }}>Hipercard</option>
                            <option value="Outro" {{ old('brand', $paymentMethod->brand) == 'Outro' ? 'selected' : '' }}>Outro</option>
                        </select>
                    </div>

                    <!-- Últimos 4 dígitos -->
                    <div>
                        <label for="last_four_digits" class="block text-sm font-medium text-gray-700">Últimos 4 dígitos</label>
                        <input type="text" 
                               name="last_four_digits" 
                               id="last_four_digits" 
                               maxlength="4"
                               pattern="[0-9]{4}"
                               value="{{ old('last_four_digits', $paymentMethod->last_four_digits) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               placeholder="1234">
                    </div>
                </div>

                <!-- Campos específicos de Cartão de Crédito -->
                <div x-show="selectedType === 'credit_card'" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="due_day" class="block text-sm font-medium text-gray-700">Dia de Vencimento</label>
                        <input type="number" 
                               name="due_day" 
                               id="due_day" 
                               min="1" 
                               max="31"
                               value="{{ old('due_day', $paymentMethod->due_day) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               placeholder="10">
                    </div>

                    <div>
                        <label for="closing_day" class="block text-sm font-medium text-gray-700">Dia de Fechamento</label>
                        <input type="number" 
                               name="closing_day" 
                               id="closing_day" 
                               min="1" 
                               max="31"
                               value="{{ old('closing_day', $paymentMethod->closing_day) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               placeholder="5">
                    </div>

                    <div>
                        <label for="credit_limit" class="block text-sm font-medium text-gray-700">Limite (R$)</label>
                        <input type="number" 
                               name="credit_limit" 
                               id="credit_limit" 
                               step="0.01"
                               min="0"
                               value="{{ old('credit_limit', $paymentMethod->credit_limit) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               placeholder="5000.00">
                    </div>
                </div>
            </div>

            <!-- Cor -->
            <div>
                <label for="color" class="block text-sm font-medium text-gray-700">
                    Cor do Card <span class="text-red-500">*</span>
                </label>
                <div class="mt-2 flex items-center space-x-3">
                    <input type="color" 
                           name="color" 
                           id="color" 
                           x-model="selectedColor"
                           value="{{ old('color', $paymentMethod->color) }}"
                           class="h-10 w-20 rounded border-gray-300 cursor-pointer">
                    
                    <input type="text" 
                           x-model="selectedColor"
                           class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="#6366f1">
                </div>
                
                <!-- Cores sugeridas -->
                <div class="mt-3">
                    <p class="text-xs text-gray-500 mb-2">Cores sugeridas:</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="color in suggestedColors" :key="color">
                            <button type="button"
                                    @click="selectedColor = color"
                                    class="w-8 h-8 rounded-full border-2"
                                    :class="selectedColor === color ? 'border-gray-900' : 'border-gray-200'"
                                    :style="`background-color: ${color}`">
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       id="is_active" 
                       value="1"
                       {{ old('is_active', $paymentMethod->is_active) ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                    Método ativo
                </label>
            </div>

            <!-- Info de despesas -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Este método possui <strong>{{ $paymentMethod->expenses_count }}</strong> despesa(s) associada(s).
                        </p>
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <p class="text-sm font-medium text-gray-700 mb-3">Preview do Card:</p>
                <div class="max-w-sm mx-auto">
                    <div class="rounded-xl shadow-lg overflow-hidden" 
                         :style="`background: linear-gradient(135deg, ${selectedColor} 0%, ${selectedColor}dd 100%)`">
                        <div class="p-6 text-white">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center space-x-2">
                                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                        <span class="text-2xl" x-text="getTypeIcon(selectedType)"></span>
                                    </div>
                                    <div>
                                        <p class="text-xs opacity-75" x-text="typeOptions[selectedType]"></p>
                                    </div>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-white bg-opacity-20">
                                    {{ $paymentMethod->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                            <h3 class="text-xl font-bold mb-4">{{ $paymentMethod->name }}</h3>
                            <p class="text-sm font-mono tracking-wider">
                                @if($paymentMethod->last_four_digits)
                                    •••• •••• •••• {{ $paymentMethod->last_four_digits }}
                                @else
                                    •••• •••• •••• ••••
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-between pt-5 border-t border-gray-200">
                <a href="{{ route('payment-methods.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Atualizar Método
                </button>
            </div>
        </form>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection

@push('scripts')
<script>
function paymentMethodForm() {
    return {
        selectedType: '{{ old('type', $paymentMethod->type) }}',
        selectedColor: '{{ old('color', $paymentMethod->color) }}',
        typeOptions: {
            'credit_card': 'Cartão de Crédito',
            'debit_card': 'Cartão de Débito',
            'pix': 'PIX',
            'cash': 'Dinheiro',
            'bank_transfer': 'Transferência',
            'bank_slip': 'Boleto',
            'other': 'Outro'
        },
        suggestedColors: [
            '#1e40af', // Blue
            '#7c3aed', // Purple
            '#dc2626', // Red
            '#ea580c', // Orange
            '#65a30d', // Green
            '#0891b2', // Cyan
            '#4f46e5', // Indigo
            '#be123c', // Rose
        ],
        
        getTypeIcon(type) {
            const icons = {
                'credit_card': '💳',
                'debit_card': '💳',
                'pix': '🔐',
                'cash': '💵',
                'bank_transfer': '🏦',
                'bank_slip': '📄',
                'other': '📌'
            };
            return icons[type] || '📌';
        }
    }
}
</script>
@endpush