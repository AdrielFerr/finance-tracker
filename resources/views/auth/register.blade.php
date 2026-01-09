@php
use Illuminate\Support\Facades\Storage;
$logoPath = setting('logo_path');
$appName = setting('app_name', 'FinanceTracker');
$primaryColor = setting('primary_color', '#4F46E5');
@endphp

<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrar - {{ $appName }}</title>

    <!-- Favicon Dinâmico -->
    @php
        $faviconPath = setting('favicon_path');
    @endphp
    
    @if($faviconPath && Storage::disk('public')->exists($faviconPath))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $faviconPath) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='45' fill='{{ urlencode($primaryColor) }}'/%3E%3Ctext x='50' y='50' text-anchor='middle' dy='.35em' font-size='50' fill='white' font-family='Arial, sans-serif' font-weight='bold'%3E$%3C/text%3E%3C/svg%3E">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
    <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Logo Dinâmica -->
            <div class="flex justify-center">
                @if($logoPath && Storage::disk('public')->exists($logoPath))
                    <div class="h-20 w-20 rounded-full flex items-center justify-center" 
                         style="background-color: {{ $primaryColor }}">
                        <img src="{{ asset('storage/' . $logoPath) }}" 
                             alt="{{ $appName }}" 
                             class="h-14 w-14 object-contain">
                    </div>
                @else
                    <div class="h-20 w-20 rounded-full flex items-center justify-center" 
                         style="background-color: {{ $primaryColor }}">
                        <svg class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                @endif
            </div>

            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
                Criar conta gratuita
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Comece a gerenciar suas finanças agora
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <!-- Nome -->
                    <div>
                        <label for="name" class="block text-sm font-medium leading-6 text-gray-900">
                            Nome completo
                        </label>
                        <div class="mt-2">
                            <input id="name" 
                                   name="name" 
                                   type="text" 
                                   autocomplete="name" 
                                   required 
                                   value="{{ old('name') }}"
                                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- E-mail -->
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
                                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Senha -->
                    <div>
                        <label for="password" class="block text-sm font-medium leading-6 text-gray-900">
                            Senha
                        </label>
                        <div class="mt-2">
                            <input id="password" 
                                   name="password" 
                                   type="password" 
                                   autocomplete="new-password" 
                                   required
                                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirmar Senha -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">
                            Confirmar senha
                        </label>
                        <div class="mt-2">
                            <input id="password_confirmation" 
                                   name="password_confirmation" 
                                   type="password" 
                                   autocomplete="new-password" 
                                   required
                                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <!-- Botão Registrar -->
                    <div>
                        <button type="submit" 
                                class="flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition"
                                style="background-color: {{ $primaryColor }}">
                            Criar conta
                        </button>
                    </div>
                </form>

                <p class="mt-6 text-center text-sm text-gray-500">
                    Já tem uma conta? 
                    <a href="{{ route('login') }}" 
                       class="font-semibold leading-6 hover:opacity-80"
                       style="color: {{ $primaryColor }}">
                        Fazer login
                    </a>
                </p>
            </div>

            <p class="mt-6 text-center text-xs text-gray-500">
                &copy; {{ date('Y') }} {{ $appName }}. Todos os direitos reservados.
            </p>
        </div>
    </div>
</body>
</html>