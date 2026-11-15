<div x-data="{ open: {{ $errors->hasAny(['description', 'amount', 'due_date', 'category_id', 'type', 'total_installments', 'receipt', 'notes']) || request('nova') ? 'true' : 'false' }} }"
     @abrir-nova-despesa.window="open = true"
     x-show="open"
     x-cloak
     class="relative z-50">

    <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="open = false"></div>

    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-start justify-center p-4 py-10">
            <div class="relative w-full max-w-2xl bg-white rounded-lg shadow-xl" @click.stop>
                <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data"
                      x-data="novaDespesaModal()">
                    @csrf

                    <div class="px-6 py-4 border-b border-gray-200 flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Nova despesa</h3>
                            <p class="mt-1 text-sm text-gray-500">O essencial primeiro. O resto fica em "mais opções".</p>
                        </div>
                        <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-4 space-y-4">
                        <div>
                            <label for="modal_description" class="block text-sm font-medium text-gray-700">Descrição *</label>
                            <input type="text" name="description" id="modal_description" required
                                   value="{{ old('description') }}"
                                   placeholder="Ex: Aluguel, Supermercado, Netflix..."
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('description') border-red-300 @enderror">
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label for="modal_amount" class="block text-sm font-medium text-gray-700">Valor *</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">R$</span>
                                    </div>
                                    <input type="text" name="amount" id="modal_amount" required
                                           value="{{ old('amount') }}" placeholder="0,00"
                                           x-model="amount" @input="formatCurrency"
                                           class="block w-full pl-10 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('amount') border-red-300 @enderror">
                                </div>
                                @error('amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="modal_due_date" class="block text-sm font-medium text-gray-700">Vencimento *</label>
                                <input type="date" name="due_date" id="modal_due_date" required
                                       value="{{ old('due_date', date('Y-m-d')) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('due_date') border-red-300 @enderror">
                                @error('due_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="modal_category_id" class="block text-sm font-medium text-gray-700">Categoria *</label>
                                <select name="category_id" id="modal_category_id" required
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
                        </div>

                        <button type="button" @click="maisOpcoes = !maisOpcoes"
                                class="flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                            <svg class="w-4 h-4 mr-1 transition-transform" :class="maisOpcoes ? 'rotate-90' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            <span x-text="maisOpcoes ? 'Menos opções' : 'Mais opções'"></span>
                        </button>

                        <div x-show="maisOpcoes" x-cloak class="space-y-4 pt-2 border-t border-gray-200">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <label for="modal_payment_method_id" class="block text-sm font-medium text-gray-700">Forma de pagamento</label>
                                    <select name="payment_method_id" id="modal_payment_method_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Selecione...</option>
                                        @foreach($paymentMethods as $method)
                                            <option value="{{ $method->id }}" {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>
                                                {{ $method->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="modal_type" class="block text-sm font-medium text-gray-700">Tipo</label>
                                    <select name="type" id="modal_type"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Fixa</option>
                                        <option value="variable" {{ old('type', 'variable') == 'variable' ? 'selected' : '' }}>Variável</option>
                                        <option value="occasional" {{ old('type') == 'occasional' ? 'selected' : '' }}>Eventual</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="modal_status" class="block text-sm font-medium text-gray-700">Status</label>
                                    <select name="status" id="modal_status" x-model="status"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pendente</option>
                                        <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Pago</option>
                                    </select>
                                </div>
                            </div>

                            <div x-show="status === 'paid'" x-cloak>
                                <label for="modal_payment_date" class="block text-sm font-medium text-gray-700">Data do pagamento</label>
                                <input type="date" name="payment_date" id="modal_payment_date"
                                       value="{{ old('payment_date', date('Y-m-d')) }}"
                                       class="mt-1 block w-full sm:w-56 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="is_installment" id="modal_is_installment" value="1"
                                           x-model="isInstallment" {{ old('is_installment') ? 'checked' : '' }}
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="modal_is_installment" class="ml-2 text-sm font-medium text-gray-700">
                                        Parcelar esta despesa
                                    </label>
                                </div>

                                <div x-show="isInstallment" x-cloak class="mt-3">
                                    <label for="modal_total_installments" class="block text-sm font-medium text-gray-700">Parcelas</label>
                                    <input type="number" name="total_installments" id="modal_total_installments"
                                           min="2" max="60" x-model="totalInstallments"
                                           value="{{ old('total_installments', 2) }}"
                                           class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <p class="mt-1 text-sm text-gray-500" x-show="amount">
                                        Cada parcela: <span class="font-semibold" x-text="valorDaParcela()"></span>
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label for="modal_notes" class="block text-sm font-medium text-gray-700">Observações</label>
                                <textarea name="notes" id="modal_notes" rows="2"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes') }}</textarea>
                            </div>

                            <div>
                                <label for="modal_receipt" class="block text-sm font-medium text-gray-700">Comprovante</label>
                                <input type="file" name="receipt" id="modal_receipt" accept=".pdf,.jpg,.jpeg,.png"
                                       class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                @error('receipt')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                        <button type="button" @click="open = false"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Salvar despesa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function novaDespesaModal() {
    return {
        amount: '{{ old('amount') }}',
        status: '{{ old('status', 'pending') }}',
        isInstallment: {{ old('is_installment') ? 'true' : 'false' }},
        totalInstallments: {{ old('total_installments', 2) }},
        maisOpcoes: {{ $errors->hasAny(['total_installments', 'receipt', 'notes', 'payment_method_id']) ? 'true' : 'false' }},

        formatCurrency() {
            let value = this.amount.replace(/\D/g, '');
            this.amount = (value / 100).toFixed(2).replace('.', ',');
        },

        valorDaParcela() {
            if (!this.amount || !this.totalInstallments) return 'R$ 0,00';
            let value = parseFloat(this.amount.replace(/\./g, '').replace(',', '.'));
            return 'R$ ' + (value / this.totalInstallments).toFixed(2).replace('.', ',');
        }
    }
}
</script>
@endpush
