<div class="mt-6 bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 4v-4z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900">Avisos por WhatsApp</h3>
        </div>
        <p class="mt-1 text-sm text-gray-500">
            Servidor WAHA usado para enviar os avisos de vencimento. A chave fica guardada criptografada.
        </p>
    </div>

    <form method="POST" action="{{ route('admin.system-config.whatsapp.update') }}" class="p-6 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="waha_url" class="block text-sm font-medium text-gray-700">Endereço do servidor</label>
            <input type="url" name="waha_url" id="waha_url"
                   value="{{ old('waha_url', $whatsapp['url']) }}"
                   placeholder="https://apiwaha.exemplo.com.br"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('waha_url') border-red-300 @enderror">
            @error('waha_url')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="waha_session" class="block text-sm font-medium text-gray-700">Sessão</label>
            <input type="text" name="waha_session" id="waha_session"
                   value="{{ old('waha_session', $whatsapp['session']) }}"
                   placeholder="default"
                   class="mt-1 block w-56 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('waha_session') border-red-300 @enderror">
            <p class="mt-1 text-sm text-gray-500">O nome da sessão no painel do WAHA. Normalmente é <code>default</code>.</p>
            @error('waha_session')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="waha_api_key" class="block text-sm font-medium text-gray-700">Chave de acesso</label>

            @if($whatsapp['has_key'])
                <div class="mt-1 flex items-center text-sm text-green-700 bg-green-50 border border-green-200 rounded-md px-3 py-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Chave guardada. Preencha abaixo apenas se quiser trocar.
                </div>
            @elseif($whatsapp['key_unreadable'])
                <div class="mt-1 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md px-3 py-2">
                    A chave guardada não pôde ser lida (a APP_KEY do sistema mudou). Informe a chave de novo.
                </div>
            @else
                <div class="mt-1 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-3 py-2">
                    Nenhuma chave guardada. Sem ela, os avisos por WhatsApp não saem.
                </div>
            @endif

            <input type="password" name="waha_api_key" id="waha_api_key" autocomplete="new-password"
                   placeholder="{{ $whatsapp['has_key'] ? 'Deixe em branco para manter a atual' : 'Cole a chave do WAHA' }}"
                   class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('waha_api_key') border-red-300 @enderror">
            @error('waha_api_key')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-gray-200">
            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                Salvar
            </button>

            <button type="submit" form="waha-test"
                    class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Testar conexão
            </button>

            @if($whatsapp['has_key'])
                <button type="submit" form="waha-forget-key"
                        onsubmit="return confirm('Remover a chave guardada?')"
                        class="ml-auto text-sm text-red-600 hover:text-red-800 font-medium">
                    Remover chave
                </button>
            @endif
        </div>
    </form>

    <form id="waha-test" method="POST" action="{{ route('admin.system-config.whatsapp.test') }}" class="hidden">
        @csrf
    </form>

    <form id="waha-forget-key" method="POST" action="{{ route('admin.system-config.whatsapp.forget-key') }}" class="hidden"
          onsubmit="return confirm('Remover a chave guardada? Os avisos por WhatsApp param até você informar outra.')">
        @csrf
        @method('DELETE')
    </form>
</div>
