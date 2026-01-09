@extends('layouts.app')

@section('title', 'Editar Despesa')
@section('page-title', 'Editar Despesa')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data" x-data="expenseForm()">
            @csrf
            @method('PUT')

            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Editar Despesa</h3>
                <p class="mt-1 text-sm text-gray-500">Atualize os dados da despesa</p>
            </div>

            <div class="px-6 py-4 space-y-6">
                <!-- Descrição -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Descrição *</label>
                    <input type="text" name="description" id="description" value="{{ old('description', $expense->description) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('description') border-red-300 @enderror">
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
                                <option value="{{ $category->id }}" {{ old('category_id', $expense->category_id) == $category->id ? 'selected' : '' }}>
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
                            <input type="text" name="amount" id="amount" value="{{ old('amount', number_format($expense->amount, 2, ',', '')) }}" required
                                   class="block w-full pl-12 pr-12 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('amount') border-red-300 @enderror"
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
                                <option value="{{ $method->id }}" {{ old('payment_method_id', $expense->payment_method_id) == $method->id ? 'selected' : '' }}>
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
                            <option value="fixed" {{ old('type', $expense->type) == 'fixed' ? 'selected' : '' }}>Fixa</option>
                            <option value="variable" {{ old('type', $expense->type) == 'variable' ? 'selected' : '' }}>Variável</option>
                            <option value="occasional" {{ old('type', $expense->type) == 'occasional' ? 'selected' : '' }}>Eventual</option>
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
                        <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $expense->due_date->format('Y-m-d')) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('due_date') border-red-300 @enderror">
                        @error('due_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                        <select name="status" id="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="pending" {{ old('status', $expense->status) == 'pending' ? 'selected' : '' }}>Pendente</option>
                            <option value="paid" {{ old('status', $expense->status) == 'paid' ? 'selected' : '' }}>Pago</option>
                            <option value="overdue" {{ old('status', $expense->status) == 'overdue' ? 'selected' : '' }}>Vencido</option>
                            <option value="canceled" {{ old('status', $expense->status) == 'canceled' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Data de Pagamento (condicional) -->
                <div x-show="document.getElementById('status').value === 'paid'">
                    <label for="payment_date" class="block text-sm font-medium text-gray-700">Data de Pagamento</label>
                    <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', $expense->payment_date?->format('Y-m-d')) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('payment_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notas -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Observações</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes', $expense->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Comprovante Atual -->
                @if($expense->receipt_path)
                    <div class="border-t border-gray-200 pt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Comprovante Atual</label>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="h-10 w-10 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Comprovante anexado</p>
                                    <p class="text-xs text-gray-500">Arquivo disponível para download</p>
                                </div>
                            </div>
                            <a href="{{ route('expenses.download-receipt', $expense) }}" 
                               class="inline-flex items-center px-3 py-2 border border-indigo-300 rounded-md text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Download
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Upload de Novo Comprovante - CORRIGIDO -->
                <div x-data="fileUpload()">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $expense->receipt_path ? 'Substituir Comprovante' : 'Adicionar Comprovante' }}
                    </label>
                    
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md transition-all"
                         :class="isDragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 hover:border-indigo-400'"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleDrop($event)">
                        
                        <div class="space-y-2 text-center w-full">
                            <!-- Ícone -->
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" 
                                      stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            
                            <!-- Texto -->
                            <div class="flex justify-center text-sm text-gray-600">
                                <label for="receipt" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500 px-2">
                                    <span>Upload de arquivo</span>
                                    <input type="file" 
                                           name="receipt" 
                                           id="receipt" 
                                           x-ref="fileInput"
                                           class="sr-only" 
                                           accept=".pdf,.jpg,.jpeg,.png,.gif"
                                           @change="handleFileSelect($event)">
                                </label>
                                <p class="pl-1">ou arraste aqui</p>
                            </div>
                            
                            <p class="text-xs text-gray-500">
                                PDF, PNG, JPG até 5MB
                                @if($expense->receipt_path)
                                    <span class="text-amber-600 font-medium">• Substituirá o arquivo atual</span>
                                @endif
                            </p>
                            
                            <!-- Arquivo selecionado -->
                            <div x-show="fileName" x-cloak class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div class="text-left">
                                            <p class="text-sm font-medium text-green-900" x-text="fileName"></p>
                                            <p class="text-xs text-green-600" x-text="fileSize"></p>
                                        </div>
                                    </div>
                                    <button type="button" 
                                            @click="removeFile()"
                                            class="text-green-600 hover:text-green-800 p-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @error('receipt')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botões -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                <a href="{{ route('expenses.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Atualizar Despesa
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
        amount: '{{ old('amount', number_format($expense->amount, 2, ',', '')) }}',
        
        formatCurrency() {
            let value = this.amount.replace(/\D/g, '');
            value = (value / 100).toFixed(2);
            this.amount = value.replace('.', ',');
        }
    }
}

function fileUpload() {
    return {
        fileName: '',
        fileSize: '',
        isDragging: false,
        
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.updateFileInfo(file);
            }
        },
        
        handleDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer.files[0];
            if (file) {
                this.$refs.fileInput.files = event.dataTransfer.files;
                this.updateFileInfo(file);
            }
        },
        
        updateFileInfo(file) {
            this.fileName = file.name;
            const sizeKB = (file.size / 1024).toFixed(2);
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            this.fileSize = file.size < 1024 * 1024 
                ? `${sizeKB} KB` 
                : `${sizeMB} MB`;
        },
        
        removeFile() {
            this.fileName = '';
            this.fileSize = '';
            this.$refs.fileInput.value = '';
        }
    }
}
</script>
@endpush