@php
use Illuminate\Support\Facades\Storage;
$logoPath = setting('logo_path');
$appName = setting('app_name', 'FinanceTracker');
// Cor roxa original do seu sistema
$primaryColor = setting('primary_color', '#4F46E5'); 
$logoDisplayMode = setting('logo_display_mode', 'logo_only');
@endphp

<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ $appName }}</title>

    @php $faviconPath = setting('favicon_path'); @endphp
    @if($faviconPath && Storage::disk('public')->exists($faviconPath))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $faviconPath) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='45' fill='{{ urlencode($primaryColor) }}'/%3E%3Ctext x='50' y='50' text-anchor='middle' dy='.35em' font-size='50' fill='white' font-family='Arial, sans-serif' font-weight='bold'%3E$%3C/text%3E%3C/svg%3E">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full flex items-center justify-center bg-cover" 
      style="background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $primaryColor }}dd 100%);">

    <div class="w-full max-w-6xl p-4 sm:p-8 flex flex-col lg:flex-row items-center justify-between gap-12">

        <div class="flex-1 text-center lg:text-left text-white space-y-6">
            
            @if($logoDisplayMode === 'logo_and_name' || !$logoPath)
                <h1 class="text-4xl lg:text-5xl font-bold mb-4 drop-shadow-md">Bem-vindo ao {{ $appName }}</h1>
                <p class="text-xl opacity-90 font-light">Controle suas finanças de forma simples e inteligente</p>
            @else
                {{-- <div class="mb-8 flex justify-center lg:justify-start">
                    @if($logoPath && Storage::disk('public')->exists($logoPath))
                         <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 inline-block border border-white/20">
                             <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $appName }}" class="h-16 object-contain">
                         </div>
                    @else
                         <svg class="h-20 w-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </div> --}}
                <h1 class="text-4xl font-bold mb-2">Bem-vindo!</h1>
                <p class="text-lg opacity-80">Acesse sua conta para continuar gerenciando seus ativos.</p>
            @endif

            <div class="relative w-full max-w-md mx-auto lg:mx-0 my-8 hover:scale-105 transition-transform duration-500">
                <img src="{{ asset('finance-app-animate1.svg') }}" 
                     alt="Ilustração Financeira" 
                     class="w-full h-auto drop-shadow-2xl">
            </div>

            <div class="hidden lg:flex flex-col space-y-3 pt-2 pl-2">
                <div class="flex items-center space-x-3">
                    <div class="bg-green-400/20 p-1 rounded-full">
                        <svg class="w-5 h-5 text-green-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <span class="opacity-90 text-lg font-medium">Controle total de despesas</span>
                    <div class="bg-green-400/20 p-1 rounded-full">
                        <svg class="w-5 h-5 text-green-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <span class="opacity-90 text-lg font-medium">Relatórios detalhados</span>
                </div>
            </div>
        </div>

        <div class="w-full max-w-md bg-white rounded-3xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.5)] p-8 sm:p-12 relative z-10 animate-fade-in-up">
            
            <div class="text-center mb-8">
                @if($logoPath && Storage::disk('public')->exists($logoPath))
                    <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $appName }}" class="h-12 mx-auto mb-4 object-contain">
                @endif
                
                <h3 class="text-2xl font-bold text-gray-800">Faça Login</h3>
                <p class="text-gray-500 mt-2 text-sm">Preencha seus dados para entrar</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1 ml-1">E-mail</label>
                    <div class="relative group">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-[{{ $primaryColor }}] transition-colors">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" /><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" /></svg>
                        </div>
                        <input id="email" name="email" type="email" required value="{{ old('email') }}"
                               class="block w-full rounded-xl border-0 py-3.5 pl-11 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 bg-gray-50 hover:bg-white transition-all"
                               style="--tw-ring-color: {{ $primaryColor }}; focus:--tw-ring-color: {{ $primaryColor }};"
                               placeholder="seu@email.com">
                    </div>
                    @error('email') <p class="mt-1 text-xs text-red-600 ml-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1 ml-1">Senha</label>
                    <div class="relative group">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-[{{ $primaryColor }}] transition-colors">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 4.5H7V9h6V5.5z" clip-rule="evenodd" /></svg>
                        </div>
                        <input id="password" name="password" type="password" required
                               class="block w-full rounded-xl border-0 py-3.5 pl-11 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 bg-gray-50 hover:bg-white transition-all"
                               style="--tw-ring-color: {{ $primaryColor }}; focus:--tw-ring-color: {{ $primaryColor }};"
                               placeholder="••••••••">
                    </div>
                    @error('password') <p class="mt-1 text-xs text-red-600 ml-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember" type="checkbox"
                               class="h-4 w-4 rounded border-gray-300 cursor-pointer"
                               style="color: {{ $primaryColor }};">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-600 cursor-pointer select-none">Lembrar</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-semibold hover:opacity-80 transition-opacity" style="color: {{ $primaryColor }};">
                            Esqueceu a senha?
                        </a>
                    @endif
                </div>

                <button type="submit"
                        class="flex w-full justify-center rounded-xl px-4 py-4 text-sm font-bold text-white shadow-lg transition-all hover:-translate-y-1 hover:shadow-xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                        style="background-color: {{ $primaryColor }};">
                    ENTRAR
                </button>
            </form>

            <div class="mt-8 text-center border-t border-gray-100 pt-6">
                <p class="text-sm text-gray-500">
                    Ainda não possui conta?
                    <a href="{{ route('register') }}" class="font-bold hover:underline ml-1" style="color: {{ $primaryColor }};">
                        Criar agora
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>