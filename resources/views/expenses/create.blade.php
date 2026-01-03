@extends('layouts.app')

@section('title', 'Nova Despesa')
@section('page-title', 'Cadastrar Nova Despesa')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data" x-data="expenseForm()">
            @csrf

            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Informações da Despesa</h3>
                <p class="mt-1 text-sm text-gray-500">Preencha os dados da nova despesa</p>
            </div>

            <div class="px-6 py-4 space-y-6">
                <!-- Descrição -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Descrição *</label>
                    <input type="text" name="description" id="description" value="{{ old('description') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('description') border-red-300 @enderror"
                           placeholder="Ex: Aluguel, Supermercado, Netflix...">
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Categoria e Valor -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Categoria *</label>
                        <select name="category_id" id="category_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('category_id') border-red-300 @enderror">
                            <option value="">Selecione...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">Valor *</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">R$</span>
                            </div>
                            <input type="text" name="amount" id="amount" value="{{ old('amount') }}" required
                                   class="block w-full pl-12 pr-12 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('amount') border-red-300 @enderror"
                                   placeholder="0,00"
                                   x-model="amount"
                                   @input="formatCurrency">
                        </div>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Método de Pagamento e Tipo -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="payment_method_id" class="block text-sm font-medium text-gray-700">Método de Pagamento</label>
                        <select name="payment_method_id" id="payment_method_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Selecione...</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}" {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>
                                    {{ $method->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_method_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Tipo *</label>
                        <select name="type" id="type" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('type') border-red-300 @enderror">
                            <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Fixa</option>
                            <option value="variable" {{ old('type', 'variable') == 'variable' ? 'selected' : '' }}>Variável</option>
                            <option value="occasional" {{ old('type') == 'occasional' ? 'selected' : '' }}>Eventual</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Data de Vencimento e Status -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700">Data de Vencimento *</label>
                        <input type="date" name="due_date" id="due_date" value="{{ old('due_date', date('Y-m-d')) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('due_date') border-red-300 @enderror">
                        @error('due_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                        <select name="status" id="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pendente</option>
                            <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Pago</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Data de Pagamento (condicional) -->
                <div x-show="document.getElementById('status').value === 'paid'">
                    <label for="payment_date" class="block text-sm font-medium text-gray-700">Data de Pagamento</label>
                    <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('payment_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Parcelamento -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_installment" id="is_installment" value="1" {{ old('is_installment') ? 'checked' : '' }}
                               x-model="isInstallment"
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_installment" class="ml-2 block text-sm font-medium text-gray-700">
                            Parcelar esta despesa
                        </label>
                    </div>

                    <div x-show="isInstallment" class="mt-4" x-cloak>
                        <label for="total_installments" class="block text-sm font-medium text-gray-700">Número de Parcelas</label>
                        <input type="number" name="total_installments" id="total_installments" min="2" max="60" value="{{ old('total_installments', 2) }}"
                               x-model="totalInstallments"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <p class="mt-2 text-sm text-gray-500" x-show="totalInstallments > 0 && amount">
                            Valor de cada parcela: <span class="font-semibold" x-text="calculateInstallment()"></span>
                        </p>
                    </div>
                </div>

                <!-- Notas -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Observações</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                              placeholder="Adicione observações sobre esta despesa...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Upload de Comprovante -->
                <div>
                    <label for="receipt" class="block text-sm font-medium text-gray-700">Comprovante</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="receipt" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500">
                                    <span>Upload de arquivo</span>
                                    <input type="file" name="receipt" id="receipt" class="sr-only" accept=".pdf,.jpg,.jpeg,.png">
                                </label>
                                <p class="pl-1">ou arraste aqui</p>
                            </div>
                            <p class="text-xs text-gray-500">PDF, PNG, JPG até 5MB</p>
                        </div>
                    </div>
                    @error('receipt')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botões -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                <a href="{{ route('expenses.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Salvar Despesa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function expenseForm() {
    return {
        amount: '{{ old('amount') }}',
        isInstallment: {{ old('is_installment') ? 'true' : 'false' }},
        totalInstallments: {{ old('total_installments', 2) }},
        
        formatCurrency() {
            let value = this.amount.replace(/\D/g, '');
            value = (value / 100).toFixed(2);
            this.amount = value.replace('.', ',');
        },
        
        calculateInstallment() {
            if (!this.amount || !this.totalInstallments) return 'R$ 0,00';
            let value = parseFloat(this.amount.replace(',', '.'));
            let installment = value / this.totalInstallments;
            return 'R$ ' + installment.toFixed(2).replace('.', ',');
        }
    }
}
</script>
@endpush