<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Se não estiver logado, continua
        if (!$user) {
            return $next($request);
        }

        // Super Admin sempre passa
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Verificar se usuário está inativo
        // if (!$user->is_active) {
        //     Auth::logout();
        //     return redirect()->route('login')
        //         ->withErrors(['email' => 'Sua conta está suspensa. Entre em contato com o administrador.']);
        // }

        // Verificar se tem tenant e se está suspenso
        if ($user->tenant_id and !$user->is_active) {
            $tenant = $user->tenant;
            
            if (!$tenant || $tenant->status === 'suspended') {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Sua empresa está suspensa. Entre em contato com o suporte.']);
            }

            if ($tenant->status === 'cancelled') {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Sua empresa foi cancelada. Entre em contato com o suporte.']);
            }
        } elseif (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')
            ->withErrors(['email' => 'Sua conta está suspensa. Entre em contato com o administrador.']);
        }

        return $next($request);
    }
}