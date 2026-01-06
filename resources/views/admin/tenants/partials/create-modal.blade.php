<!-- Modal de Criação de Tenant -->
<div x-data="{ showModal: @json($errors->any() || session('showModal', false)) }"
     x-init="$watch('showModal', value => document.body.style.overflow = value ? 'hidden' : 'auto')"
     @open-tenant-modal.window="showModal = true"
     @keydown.escape.window="showModal = false">
    
    <div x-show="showModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @click.self="showModal = false">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div x-show="showModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="showModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Nova Empresa
                        </h3>
                        <button @click="showModal = false" type="button" class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('admin.tenants.store') }}" class="p-6 bg-gray-50" x-data="tenantForm()">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- DADOS DA EMPRESA -->
                        <div class="md:col-span-2 mb-2">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-2">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <h4 class="ml-3 text-md font-semibold text-gray-900">Dados da Empresa</h4>
                            </div>
                        </div>

                        <!-- Nome da Empresa -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nome da Empresa <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" required
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition"
                                   placeholder="Ex: Empresa ABC Ltda"
                                   value="{{ old('name') }}">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email da Empresa -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                E-mail <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   required
                                   x-model="email"
                                   @input="validateEmail()"
                                   :class="emailError ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                                   class="block w-full rounded-lg shadow-sm sm:text-sm"
                                   placeholder="contato@empresa.com"
                                   value="{{ old('email') }}">
                            <p x-show="emailError" class="mt-1 text-sm text-red-600" x-text="emailError"></p>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Telefone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                            <input type="text" 
                                   name="phone" 
                                   id="phone"
                                   x-model="phone"
                                   @input="maskPhone()"
                                   maxlength="15"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                   placeholder="(83) 98888-8888"
                                   value="{{ old('phone') }}">
                            <p class="mt-1 text-xs text-gray-500">Formato: (DDD) 9XXXX-XXXX</p>
                        </div>

                        <!-- Endereço -->
                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                            <textarea name="address" id="address" rows="2"
                                      class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                      placeholder="Rua, número, bairro, cidade - UF">{{ old('address') }}</textarea>
                        </div>

                        <!-- PLANO E LIMITES -->
                        <div class="md:col-span-2 mb-2 mt-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-purple-100 rounded-lg p-2">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                </div>
                                <h4 class="ml-3 text-md font-semibold text-gray-900">Plano e Limites</h4>
                            </div>
                        </div>

                        <!-- Plano -->
                        <div>
                            <label for="plan" class="block text-sm font-medium text-gray-700 mb-1">
                                Plano <span class="text-red-500">*</span>
                            </label>
                            <select name="plan" id="plan" required
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="free" {{ old('plan') == 'free' ? 'selected' : '' }}>Free</option>
                                <option value="basic" {{ old('plan') == 'basic' ? 'selected' : '' }}>Basic</option>
                                <option value="premium" {{ old('plan', 'premium') == 'premium' ? 'selected' : '' }}>Premium</option>
                                <option value="enterprise" {{ old('plan') == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                            </select>
                        </div>

                        <!-- Máximo de Usuários -->
                        <div>
                            <label for="max_users" class="block text-sm font-medium text-gray-700 mb-1">
                                Máx. Usuários <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="max_users" id="max_users" required min="1" 
                                   value="{{ old('max_users', 10) }}"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- Máximo de Despesas -->
                        <div class="md:col-span-2">
                            <label for="max_expenses" class="block text-sm font-medium text-gray-700 mb-1">
                                Máx. Despesas/Mês <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="max_expenses" id="max_expenses" required min="100" 
                                   value="{{ old('max_expenses', 5000) }}"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- ADMINISTRADOR -->
                        <div class="md:col-span-2 mb-2 mt-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-100 rounded-lg p-2">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <h4 class="ml-3 text-md font-semibold text-gray-900">Administrador da Empresa</h4>
                            </div>
                        </div>

                        <!-- Nome do Admin -->
                        <div class="md:col-span-2">
                            <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nome Completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="admin_name" id="admin_name" required
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                   placeholder="Nome do administrador"
                                   value="{{ old('admin_name') }}">
                            @error('admin_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email do Admin -->
                        <div>
                            <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-1">
                                E-mail <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   name="admin_email" 
                                   id="admin_email" 
                                   required
                                   x-model="adminEmail"
                                   @input="validateAdminEmail()"
                                   :class="adminEmailError ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                                   class="block w-full rounded-lg shadow-sm sm:text-sm"
                                   placeholder="admin@empresa.com"
                                   value="{{ old('admin_email') }}">
                            <p x-show="adminEmailError" class="mt-1 text-sm text-red-600" x-text="adminEmailError"></p>
                            @error('admin_email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Senha do Admin -->
                        <div>
                            <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">
                                Senha <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="admin_password" id="admin_password" required minlength="8"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                   placeholder="Mínimo 8 caracteres">
                            @error('admin_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirmar Senha -->
                        <div class="md:col-span-2">
                            <label for="admin_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                Confirmar Senha <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" required minlength="8"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                   placeholder="Digite a senha novamente">
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex items-center justify-end space-x-3 pt-5 border-t border-gray-200">
                        <button type="button" 
                                @click="showModal = false"
                                class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 border border-transparent rounded-lg font-medium text-sm text-white hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm transition">
                            <svg class="w-5 h-5 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Criar Empresa
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

<script>
function tenantForm() {
    return {
        email: '',
        emailError: '',
        adminEmail: '',
        adminEmailError: '',
        phone: '',
        
        validateEmail() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.email && !emailRegex.test(this.email)) {
                this.emailError = 'E-mail inválido';
            } else {
                this.emailError = '';
            }
        },
        
        validateAdminEmail() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.adminEmail && !emailRegex.test(this.adminEmail)) {
                this.adminEmailError = 'E-mail inválido';
            } else {
                this.adminEmailError = '';
            }
        },
        
        maskPhone() {
            let value = this.phone.replace(/\D/g, '');
            
            if (value.length <= 10) {
                value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else {
                value = value.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
            }
            
            this.phone = value;
        }
    }
}
</script>