@extends('layouts.app')

@section('title', 'Conferir importação')
@section('page-title', 'Conferir antes de lançar')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="importReview()">

    @if($errors->any())
        <div class="rounded-md bg-red-50 border border-red-200 p-4 text-sm text-red-700">
            Confira os campos destacados abaixo antes de importar.
        </div>
    @endif

    <a href="{{ route('expenses.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Voltar para despesas
    </a>

    <div class="bg-white shadow rounded-lg px-6 py-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-medium text-gray-900">{{ $batch->original_filename }}</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $batch->source_text }} · {{ $transactions->count() }} lançamento(s) lido(s) ·
                    Forma de pagamento: {{ $batch->paymentMethod?->name ?? 'não informada' }}
                </p>
            </div>

            @if(!$batch->isReviewing())
                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                    {{ $batch->status === 'confirmed' ? 'Já confirmada' : 'Descartada' }}
                </span>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('expenses.import.confirm', $batch) }}">
        @csrf

        <div class="bg-white shadow rounded-lg">
            @if($batch->isReviewing())
                <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-end gap-4">
                    <div>
                        <label for="bulk_category" class="block text-sm font-medium text-gray-700">
                            Preencher categoria de todos
                        </label>
                        <select id="bulk_category" x-model="bulkCategory"
                                class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Selecione...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" @click="applyCategoryToAll()"
                            class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-xs font-semibold text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                        Aplicar a todos
                    </button>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                @if($batch->isReviewing())
                                    <input type="checkbox" @change="toggleAll($event)" checked
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                @endif
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($transactions as $transaction)
                            @php($importable = $batch->isReviewing() && $transaction->isOutflow() && $transaction->status === 'pending')
                            <tr class="{{ $transaction->isOutflow() ? '' : 'bg-gray-50' }}">
                                <td class="px-4 py-3">
                                    @if($importable)
                                        <input type="checkbox" name="selected[]" value="{{ $transaction->id }}"
                                               {{ is_array(old('selected')) && !in_array((string) $transaction->id, old('selected')) ? '' : 'checked' }}
                                               class="row-check h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                    {{ $transaction->occurred_at->format('d/m/Y') }}
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $transaction->description }}

                                    @if($transaction->isInstallment())
                                        <span class="ml-2 inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            parcela {{ $transaction->installment_number }}/{{ $transaction->total_installments }}
                                        </span>
                                    @endif

                                    @if($transaction->is_possible_duplicate)
                                        <span class="ml-2 inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                                            possível repetido
                                        </span>
                                    @endif

                                    @if(!$transaction->isOutflow())
                                        <span class="ml-2 inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-200 text-gray-700">
                                            entrada — não vira despesa
                                        </span>
                                    @endif

                                    @if($transaction->status === 'imported')
                                        <span class="ml-2 inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            importado
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-sm text-right whitespace-nowrap {{ $transaction->isOutflow() ? 'text-gray-900' : 'text-green-700' }}">
                                    {{ $transaction->isOutflow() ? '' : '+' }}R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                </td>

                                <td class="px-4 py-3">
                                    @if($importable)
                                        <select name="categories[{{ $transaction->id }}]"
                                                class="row-category block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('categories.'.$transaction->id) border-red-300 @enderror">
                                            <option value="">Selecione...</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('categories.'.$transaction->id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('categories.'.$transaction->id)
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @else
                                        <span class="text-sm text-gray-500">{{ $transaction->category?->name ?? '—' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($batch->isReviewing())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                    <span class="text-sm text-gray-500">
                        Só os lançamentos marcados viram despesa.
                    </span>

                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Importar selecionados
                    </button>
                </div>
            @endif
        </div>
    </form>

    @if($batch->isReviewing())
        <form method="POST" action="{{ route('expenses.import.destroy', $batch) }}"
              onsubmit="return confirm('Descartar esta importação? Nenhuma despesa será criada.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                Descartar esta importação
            </button>
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
function importReview() {
    return {
        bulkCategory: '',

        applyCategoryToAll() {
            if (!this.bulkCategory) return;
            document.querySelectorAll('.row-category').forEach(select => {
                select.value = this.bulkCategory;
            });
        },

        toggleAll(event) {
            document.querySelectorAll('.row-check').forEach(checkbox => {
                checkbox.checked = event.target.checked;
            });
        }
    }
}
</script>
@endpush
