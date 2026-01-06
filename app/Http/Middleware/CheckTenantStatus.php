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

        // Se não está autenticado, deixa passar
        if (!$user) {
            return $next($request);
        }

        // Super Admin SEMPRE passa (nunca bloqueia)
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // IMPORTANTE: Verifica PRIMEIRO o is_active
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->withErrors(['email' => 'Sua conta está suspensa. Entre em contato com o administrador.']);
        }

        // Só verifica tenant se o usuário TEM tenant_id
        if ($user->tenant_id) {
            $tenant = $user->tenant;
            
            if (!$tenant) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->withErrors(['email' => 'Sua empresa não existe mais. Entre em contato com o suporte.']);
            }
            
            // Tenant suspenso
            if ($tenant->status === 'suspended') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->withErrors(['email' => 'Sua empresa está suspensa. Entre em contato com o suporte.']);
            }

            // Tenant cancelado
            if ($tenant->status === 'cancelled') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->withErrors(['email' => 'Sua empresa foi cancelada. Entre em contato com o suporte.']);
            }
        }
        return $next($request);
    }
}