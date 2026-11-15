<h3 class="text-lg font-medium text-gray-900 mb-2">Avisos de vencimento</h3>
<p class="text-sm text-gray-600 mb-6">
    Escolha com quanta antecedência você quer ser avisado e por onde.
</p>

@if (session('status') === 'notification-preferences-updated')
    <div class="mb-6 rounded-md bg-green-50 border border-green-200 p-3 text-sm text-green-700">
        Preferências salvas.
    </div>
@endif

<form method="POST" action="{{ route('profile.update-notifications') }}" class="space-y-6"
      x-data="{ whatsapp: {{ old('notify_by_whatsapp', $user->notify_by_whatsapp) ? 'true' : 'false' }} }">
    @csrf
    @method('PUT')

    <div>
        <label for="notify_days_before" class="block text-sm font-medium text-gray-700">
            Avisar com quantos dias de antecedência
        </label>
        <input type="number" name="notify_days_before" id="notify_days_before" min="1" max="60"
               value="{{ old('notify_days_before', $user->notify_days_before) }}"
               class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('notify_days_before') border-red-300 @enderror">
        <p class="mt-1 text-sm text-gray-500">
            Você recebe um aviso com tudo que vence dentro desse prazo.
        </p>
        @error('notify_days_before')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-3 border-t border-gray-200 pt-6">
        <p class="text-sm font-medium text-gray-700">Avisar também</p>

        <label class="flex items-center">
            <input type="checkbox" name="notify_on_due_date" value="1"
                   {{ old('notify_on_due_date', $user->notify_on_due_date) ? 'checked' : '' }}
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <span class="ml-2 text-sm text-gray-700">No dia do vencimento</span>
        </label>

        <label class="flex items-center">
            <input type="checkbox" name="notify_when_overdue" value="1"
                   {{ old('notify_when_overdue', $user->notify_when_overdue) ? 'checked' : '' }}
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <span class="ml-2 text-sm text-gray-700">Quando já estiver vencida</span>
        </label>
    </div>

    <div class="space-y-4 border-t border-gray-200 pt-6">
        <p class="text-sm font-medium text-gray-700">Por onde você quer receber</p>

        <label class="flex items-center">
            <input type="checkbox" name="notify_in_app" value="1"
                   {{ old('notify_in_app', $user->notify_in_app) ? 'checked' : '' }}
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <span class="ml-2 text-sm text-gray-700">Aqui na plataforma (sininho)</span>
        </label>

        <label class="flex items-center">
            <input type="checkbox" name="notify_by_email" value="1"
                   {{ old('notify_by_email', $user->notify_by_email) ? 'checked' : '' }}
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <span class="ml-2 text-sm text-gray-700">E-mail ({{ $user->email }})</span>
        </label>

        <div>
            <label class="flex items-center">
                <input type="checkbox" name="notify_by_whatsapp" value="1" x-model="whatsapp"
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <span class="ml-2 text-sm text-gray-700">WhatsApp</span>
            </label>

            <div x-show="whatsapp" x-cloak class="mt-3 ml-6">
                <label for="whatsapp_number" class="block text-sm font-medium text-gray-700">
                    Seu número com DDD
                </label>
                <input type="text" name="whatsapp_number" id="whatsapp_number"
                       value="{{ old('whatsapp_number', $user->whatsapp_number) }}"
                       placeholder="(11) 99999-9999"
                       class="mt-1 block w-56 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('whatsapp_number') border-red-300 @enderror">
                @error('whatsapp_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="border-t border-gray-200 pt-6">
        <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
            Salvar preferências
        </button>
    </div>
</form>
