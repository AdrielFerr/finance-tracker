<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - FinanceTracker</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
    <div class="min-h-full flex">
        <!-- Lado Esquerdo - Ilustração e Texto -->
        <div class="hidden lg:flex lg:flex-1 bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 relative overflow-hidden">
            <!-- Formas de fundo animadas -->
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-0 -left-4 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl animate-blob"></div>
                <div class="absolute top-0 -right-4 w-72 h-72 bg-indigo-300 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-2000"></div>
                <div class="absolute -bottom-8 left-20 w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-4000"></div>
            </div>

            <!-- Conteúdo CENTRALIZADO -->
            <div class="relative w-full flex flex-col justify-center items-center text-white text-center px-8">
                <!-- Título -->
                <div class="mb-8">
                    <h1 class="text-5xl font-bold mb-4">
                        Bem-vindo ao<br>
                        <span class="text-purple-200">FinanceTracker</span>
                    </h1>
                    <p class="text-xl text-purple-100">
                        Controle suas finanças de forma<br>
                        simples e inteligente
                    </p>
                </div>

                <!-- Ilustração SVG CENTRALIZADA -->
                <div class="my-12">
                    <svg class="w-80 h-80" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Gráfico de Pizza Central -->
                        <circle cx="200" cy="200" r="85" fill="#A78BFA" opacity="0.2"/>
                        <path d="M200 115 L200 200 L285 200 A85 85 0 0 0 200 115 Z" fill="#8B5CF6"/>
                        <path d="M200 200 L285 200 A85 85 0 0 1 200 285 Z" fill="#6366F1"/>
                        <path d="M200 200 L200 285 A85 85 0 0 1 115 200 Z" fill="#4F46E5"/>
                        <path d="M200 200 L115 200 A85 85 0 0 1 200 115 Z" fill="#7C3AED"/>
                        
                        <!-- Moedas ao redor -->
                        <circle cx="320" cy="100" r="32" fill="#FCD34D" stroke="#F59E0B" stroke-width="3"/>
                        <text x="320" y="110" text-anchor="middle" fill="#92400E" font-size="26" font-weight="bold">$</text>
                        
                        <circle cx="360" cy="200" r="26" fill="#FCD34D" stroke="#F59E0B" stroke-width="3"/>
                        <text x="360" y="208" text-anchor="middle" fill="#92400E" font-size="20" font-weight="bold">$</text>
                        
                        <circle cx="320" cy="300" r="20" fill="#FCD34D" stroke="#F59E0B" stroke-width="2"/>
                        <text x="320" y="306" text-anchor="middle" fill="#92400E" font-size="16" font-weight="bold">$</text>
                        
                        <circle cx="80" cy="100" r="28" fill="#FCD34D" stroke="#F59E0B" stroke-width="3"/>
                        <text x="80" y="108" text-anchor="middle" fill="#92400E" font-size="22" font-weight="bold">$</text>
                        
                        <circle cx="40" cy="200" r="24" fill="#FCD34D" stroke="#F59E0B" stroke-width="2"/>
                        <text x="40" y="207" text-anchor="middle" fill="#92400E" font-size="18" font-weight="bold">$</text>
                        
                        <circle cx="80" cy="300" r="22" fill="#FCD34D" stroke="#F59E0B" stroke-width="2"/>
                        <text x="80" y="307" text-anchor="middle" fill="#92400E" font-size="17" font-weight="bold">$</text>
                        
                        <!-- Gráfico de Barras embaixo -->
                        <rect x="140" y="350" width="25" height="40" rx="4" fill="#10B981"/>
                        <rect x="175" y="335" width="25" height="55" rx="4" fill="#34D399"/>
                        <rect x="210" y="320" width="25" height="70" rx="4" fill="#10B981"/>
                        <rect x="245" y="340" width="25" height="50" rx="4" fill="#34D399"/>
                    </svg>
                </div>

                <!-- Features -->
                <div class="mt-8 space-y-3">
                    <div class="flex items-center justify-center space-x-3">
                        <svg class="w-6 h-6 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-purple-100 text-lg">Controle total de despesas</span>
                    </div>
                    <div class="flex items-center justify-center space-x-3">
                        <svg class="w-6 h-6 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-purple-100 text-lg">Relatórios detalhados</span>
                    </div>
                    <div class="flex items-center justify-center space-x-3">
                        <svg class="w-6 h-6 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-purple-100 text-lg">Interface intuitiva</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lado Direito - Form de Login -->
        <div class="flex-1 flex flex-col justify-center px-4 sm:px-6 lg:px-20 xl:px-24 bg-white">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                <!-- Logo e Título -->
                <div class="text-center mb-8">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 mb-4">
                        <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900">
                        Faça login
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Acesse sua conta e gerencie suas finanças
                    </p>
                </div>

                <!-- Form -->
                <div class="mt-8">
                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium leading-6 text-gray-900">
                                E-mail
                            </label>
                            <div class="mt-2">
                                <input id="email" 
                                       name="email" 
                                       type="email" 
                                       autocomplete="email" 
                                       required 
                                       value="{{ old('email') }}"
                                       class="block w-full rounded-lg border-0 px-4 py-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('email') ring-red-500 @enderror"
                                       placeholder="seu@email.com">
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium leading-6 text-gray-900">
                                Senha
                            </label>
                            <div class="mt-2">
                                <input id="password" 
                                       name="password" 
                                       type="password" 
                                       autocomplete="current-password" 
                                       required
                                       class="block w-full rounded-lg border-0 px-4 py-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('password') ring-red-500 @enderror"
                                       placeholder="••••••••">
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember me e Esqueceu senha -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="remember_me" 
                                       name="remember" 
                                       type="checkbox" 
                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                <label for="remember_me" class="ml-3 block text-sm leading-6 text-gray-700">
                                    Lembrar de mim
                                </label>
                            </div>

                            @if (Route::has('password.request'))
                                <div class="text-sm leading-6">
                                    <a href="{{ route('password.request') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">
                                        Esqueceu a senha?
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Botão de Login -->
                        <div>
                            <button type="submit" 
                                    class="flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all">
                                Entrar
                            </button>
                        </div>
                    </form>

                    <!-- Registro -->
                    @if (Route::has('register'))
                        <p class="mt-10 text-center text-sm text-gray-500">
                            Não tem uma conta?
                            <a href="{{ route('register') }}" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">
                                Criar conta gratuita
                            </a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>
</body>
</html>