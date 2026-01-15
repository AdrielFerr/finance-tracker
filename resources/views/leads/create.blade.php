<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Lead</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white p-6">
    <!-- Formulário -->
    <form method="POST" action="{{ route('leads.store') }}" class="space-y-6" id="createLeadForm">
        @csrf

        <!-- Informações Básicas -->
        <div class="space-y-4">
            <h3 class="text-base font-medium text-gray-900 border-b pb-2">Informações Básicas</h3>
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- Pipeline -->
                <div class="sm:col-span-2">
                    <label for="pipeline_id" class="block text-sm font-medium text-gray-700">
                        Pipeline <span class="text-red-500">*</span>
                    </label>
                    <select name="pipeline_id" id="pipeline_id" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Selecione um pipeline</option>
                        @foreach($pipelines as $pipeline)
                            <option value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Título -->
                <div class="sm:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700">
                        Título do Lead <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="Ex: Proposta de Sistema Web">
                </div>

                <!-- Descrição -->
                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                    <textarea name="description" id="description" rows="2"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                              placeholder="Detalhes sobre o lead..."></textarea>
                </div>

                <!-- Valor -->
                <div>
                    <label for="value" class="block text-sm font-medium text-gray-700">Valor Estimado (R$)</label>
                    <input type="number" name="value" id="value" step="0.01" min="0"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="0,00">
                </div>

                <!-- Probabilidade -->
                <div>
                    <label for="probability" class="block text-sm font-medium text-gray-700">Probabilidade (%)</label>
                    <input type="number" name="probability" id="probability" value="50" min="0" max="100"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Data Esperada de Fechamento -->
                <div>
                    <label for="expected_close_date" class="block text-sm font-medium text-gray-700">Data Esperada de Fechamento</label>
                    <input type="date" name="expected_close_date" id="expected_close_date"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Prioridade -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700">Prioridade</label>
                    <select name="priority" id="priority"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="low">Baixa</option>
                        <option value="medium" selected>Média</option>
                        <option value="high">Alta</option>
                        <option value="urgent">Urgente</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Informações de Contato -->
        <div class="space-y-4">
            <h3 class="text-base font-medium text-gray-900 border-b pb-2">Informações de Contato</h3>
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- Nome do Contato -->
                <div class="sm:col-span-2">
                    <label for="contact_name" class="block text-sm font-medium text-gray-700">
                        Nome do Contato <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="contact_name" id="contact_name" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="Nome completo">
                </div>

                <!-- Email -->
                <div>
                    <label for="contact_email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="contact_email" id="contact_email"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="email@exemplo.com">
                </div>

                <!-- Telefone -->
                <div>
                    <label for="contact_phone" class="block text-sm font-medium text-gray-700">Telefone</label>
                    <input type="text" name="contact_phone" id="contact_phone"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="(00) 00000-0000">
                </div>

                <!-- Cargo -->
                <div>
                    <label for="contact_position" class="block text-sm font-medium text-gray-700">Cargo</label>
                    <input type="text" name="contact_position" id="contact_position"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="Ex: Gerente de TI">
                </div>

                <!-- Nome da Empresa -->
                <div>
                    <label for="company_name" class="block text-sm font-medium text-gray-700">Nome da Empresa</label>
                    <input type="text" name="company_name" id="company_name"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="Nome da empresa">
                </div>
            </div>
        </div>

        <!-- Origem e Responsável -->
        <div class="space-y-4">
            <h3 class="text-base font-medium text-gray-900 border-b pb-2">Origem e Responsável</h3>
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- Origem -->
                <div>
                    <label for="source" class="block text-sm font-medium text-gray-700">Origem</label>
                    <select name="source" id="source"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Selecione...</option>
                        <option value="website">Website</option>
                        <option value="referral">Indicação</option>
                        <option value="social_media">Redes Sociais</option>
                        <option value="email_campaign">Campanha de Email</option>
                        <option value="cold_call">Cold Call</option>
                        <option value="event">Evento</option>
                        <option value="partner">Parceiro</option>
                        <option value="organic_search">Busca Orgânica</option>
                        <option value="paid_ads">Anúncios Pagos</option>
                        <option value="other">Outro</option>
                    </select>
                </div>

                <!-- Responsável -->
                <div>
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700">Responsável</label>
                    <select name="assigned_to" id="assigned_to"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Selecione...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex items-center justify-end space-x-3 pt-4 border-t">
            <button type="button" onclick="cancelCreate()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                Cancelar
            </button>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Criar Lead
            </button>
        </div>
    </form>

    <script>
        // Notificar o parent quando criar com sucesso
        document.getElementById('createLeadForm').addEventListener('submit', function() {
            // Após o submit bem-sucedido, notificar o parent
            setTimeout(() => {
                window.parent.postMessage('lead-created', '*');
            }, 100);
        });

        function cancelCreate() {
            window.parent.postMessage('lead-cancelled', '*');
        }
    </script>
</body>
</html>
