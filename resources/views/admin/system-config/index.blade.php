@extends('layouts.app')

@section('title', 'Configurações do Sistema')
@section('page-title', 'Configurações')

@section('content')
<div class="max-w-4xl">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Configurações do Sistema</h2>
        <p class="mt-1 text-sm text-gray-500">Personalize a identidade visual da plataforma</p>
    </div>

    <!-- Formulário -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
                Identidade Visual
            </h3>
        </div>

        <form method="POST" action="{{ route('admin.system-config.update') }}" enctype="multipart/form-data" class="p-6" x-data="configForm()">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Modo de Exibição da Logo -->
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <label class="block text-sm font-medium text-gray-900 mb-3">
                        Como deseja exibir a marca?
                    </label>  
                    <div class="space-y-3">
                        <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-white transition"
                               :class="logoDisplayMode === 'logo_only' ? 'border-indigo-500 bg-white' : 'border-gray-300'">
                            <input type="radio" 
                                   name="logo_display_mode" 
                                   value="logo_only"
                                   x-model="logoDisplayMode"
                                   class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-600">
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">Logo com nome embutido</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    ✓ Recomendado: Faça upload de uma logo que já contenha o nome da empresa<br>
                                    ✓ Exemplo: Logo + "Nome Empresa" na mesma imagem<br>
                                    ✓ Mais comum e profissional
                                </div>
                            </div>
                        </label>

                        <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-white transition"
                               :class="logoDisplayMode === 'logo_and_name' ? 'border-indigo-500 bg-white' : 'border-gray-300'">
                            <input type="radio" 
                                   name="logo_display_mode" 
                                   value="logo_and_name"
                                   x-model="logoDisplayMode"
                                   class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-600">
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">Logo + Nome separados</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    • Logo (ícone) + Nome digitado abaixo<br>
                                    • Útil se você tem apenas o símbolo/ícone
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Nome da Plataforma (só aparece se modo = logo_and_name) -->
                <div x-show="logoDisplayMode === 'logo_and_name'" x-cloak>
                    <label for="app_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nome da Plataforma <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="app_name" 
                           id="app_name" 
                           x-model="appName"
                           value="{{ old('app_name', $settings['app_name']) }}"
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Ex: FinanceTracker">
                    <p class="mt-1 text-xs text-gray-500">Nome que aparecerá ao lado da logo</p>
                    @error('app_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Logo da Plataforma -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <span x-show="logoDisplayMode === 'logo_only'">Logo com nome embutido</span>
                        <span x-show="logoDisplayMode === 'logo_and_name'">Logo (ícone/símbolo)</span>
                    </label>
                    
                    <div class="flex items-start space-x-6">
                        <!-- Preview Atual -->
                        <div class="flex-shrink-0">
                            <div class="h-20 w-20 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden border border-gray-200">
                                @if($settings['logo'])
                                    <img src="{{ asset('storage/' . $settings['logo']) }}?v={{ time() }}" 
                                         alt="Logo" 
                                         x-ref="logoPreview"
                                         class="max-h-full max-w-full object-contain">
                                @else
                                    <div x-show="!logoPreview" x-ref="logoPlaceholder">
                                        <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <img x-show="logoPreview" 
                                         x-bind:src="logoPreview"
                                         alt="Logo Preview"
                                         class="max-h-full max-w-full object-contain">
                                @endif
                            </div>
                            @if($settings['logo'])
                                <p class="mt-2 text-xs text-green-600 font-medium">✓ Logo atual</p>
                            @endif
                        </div>

                        <!-- Upload -->
                        <div class="flex-1">
                            <input type="file" 
                                   name="logo" 
                                   id="logo"
                                   accept="image/*"
                                   @change="previewLogo($event)"
                                   class="block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-lg file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100
                                          cursor-pointer">
                            
                            <div class="mt-2 space-y-1 text-xs text-gray-500">
                                <p><strong>Formato:</strong> PNG, JPG, JPEG ou SVG (PNG transparente recomendado)</p>
                                <p><strong>Tamanho:</strong> Máx. 2MB</p>
                                <div x-show="logoDisplayMode === 'logo_only'">
                                    <p class="text-indigo-600 font-medium mt-2">
                                        💡 Dica: Faça upload de uma imagem que já contenha logo + nome juntos
                                    </p>
                                    <p><strong>Dimensões recomendadas:</strong> 400x100px (horizontal) ou 200x200px</p>
                                </div>
                                <div x-show="logoDisplayMode === 'logo_and_name'">
                                    <p><strong>Dimensões recomendadas:</strong> 200x200px (quadrado) ou 100x100px</p>
                                </div>
                            </div>
                            @error('logo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remover Logo -->
                        @if($settings['logo'])
                            <div class="flex-shrink-0">
                                <button type="button"
                                        onclick="if(confirm('Remover logo atual?')) { document.getElementById('remove_logo').value='1'; this.closest('form').submit(); }"
                                        class="px-3 py-2 text-sm font-medium text-red-600 hover:text-red-800 border border-red-300 rounded-lg hover:bg-red-50 transition">
                                    Remover
                                </button>
                            </div>
                        @endif
                    </div>
                    <input type="hidden" name="remove_logo" id="remove_logo" value="0">
                </div>

                <!-- Favicon -->
                <div>
                    <label for="favicon" class="block text-sm font-medium text-gray-700 mb-1">
                        Favicon <span class="text-gray-400">(Opcional)</span>
                    </label>
                    
                    <div class="flex items-start space-x-4">
                        @if($settings['favicon'])
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 border border-gray-300 rounded flex items-center justify-center bg-white">
                                    <img src="{{ asset('storage/' . $settings['favicon']) }}?v={{ time() }}" 
                                         alt="Favicon" 
                                         class="h-6 w-6 object-contain">
                                </div>
                                <p class="mt-1 text-xs text-green-600 font-medium">✓ Ativo</p>
                            </div>
                        @endif
                        
                        <div class="flex-1">
                            <input type="file" 
                                   name="favicon" 
                                   id="favicon"
                                   accept=".ico,.png"
                                   class="block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-lg file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100">
                            <p class="mt-1 text-xs text-gray-500">Ícone da aba do navegador (16x16 ou 32x32, .ico ou .png)</p>
                            @error('favicon')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Cor Primária -->
                <div>
                    <label for="primary_color" class="block text-sm font-medium text-gray-700 mb-1">
                        Cor Primária da Sidebar
                    </label>
                    <div class="flex items-center space-x-3">
                        <input type="color" 
                               name="primary_color" 
                               id="primary_color" 
                               x-model="primaryColor"
                               value="{{ old('primary_color', $settings['primary_color']) }}"
                               class="h-10 w-20 rounded border-gray-300 cursor-pointer">
                        <input type="text" 
                               x-model="primaryColor"
                               readonly
                               class="flex-1 rounded-lg border-gray-300 bg-gray-50 text-sm font-mono">
                        <button type="button"
                                @click="primaryColor = '#4F46E5'"
                                class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Padrão
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Cor de fundo da sidebar lateral</p>
                </div>

                <!-- Preview -->
                <div class="pt-4 border-t border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preview da Sidebar</label>
                    <div class="p-4 rounded-lg" x-bind:style="`background-color: ${primaryColor}`">
                        <div class="flex items-center space-x-3">
                            <!-- Preview da logo -->
                            <div class="h-10 w-10 rounded bg-white bg-opacity-20 flex items-center justify-center overflow-hidden p-1">
                                @if($settings['logo'])
                                    <img src="{{ asset('storage/' . $settings['logo']) }}" 
                                         alt="Logo Preview" 
                                         class="max-h-full max-w-full object-contain">
                                @else
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @endif
                            </div>
                            
                            <!-- Nome só aparece se modo = logo_and_name -->
                            <span x-show="logoDisplayMode === 'logo_and_name'" 
                                  class="text-xl font-bold text-white" 
                                  x-text="appName">
                                {{ $settings['app_name'] }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-white opacity-75">
                            <span x-show="logoDisplayMode === 'logo_only'">Logo com nome embutido (sem texto adicional)</span>
                            <span x-show="logoDisplayMode === 'logo_and_name'">Logo + Nome ao lado</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="mt-6 flex justify-end space-x-3 pt-5 border-t border-gray-200">
                <a href="{{ route('admin.dashboard') }}"
                   class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Salvar Configurações
                </button>
            </div>
        </form>
    </div>

    <!-- Informações do Sistema -->
    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <h4 class="text-sm font-medium text-gray-900 mb-4">Informações do Sistema</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-gray-500 text-xs mb-1">Versão Laravel</p>
                <p class="font-medium text-gray-900">{{ app()->version() }}</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-gray-500 text-xs mb-1">Versão PHP</p>
                <p class="font-medium text-gray-900">{{ PHP_VERSION }}</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-gray-500 text-xs mb-1">Ambiente</p>
                <p class="font-medium text-gray-900 uppercase">{{ config('app.env') }}</p>
            </div>
        </div>
    </div>
</div>

<script>
function configForm() {
    return {
        appName: '{{ old("app_name", $settings["app_name"]) }}',
        primaryColor: '{{ old("primary_color", $settings["primary_color"]) }}',
        logoDisplayMode: '{{ old("logo_display_mode", $settings["logo_display_mode"] ?? "logo_only") }}',
        logoPreview: null,
        
        previewLogo(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.logoPreview = e.target.result;
                    if (this.$refs.logoPreview) {
                        this.$refs.logoPreview.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection