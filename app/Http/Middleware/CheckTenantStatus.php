<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;

class CheckTenantStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Se não está autenticado, deixar passar (middleware auth trata)
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

        // Verificar se usuário tem tenant
        if (!$user->tenant_id) {
            return redirect()->route('dashboard')
                ->withErrors(['error' => 'Você não está vinculado a nenhuma empresa.']);
        }

        // Verificar se tenant existe e está ativo
        $tenant = Tenant::find($user->tenant_id);
        
        if (!$tenant) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->withErrors(['email' => 'Empresa não encontrada. Entre em contato com o suporte.']);
        }

        // Verificar se tenant está ativo (se tiver coluna is_active)
        if (isset($tenant->is_active) && !$tenant->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->withErrors(['email' => 'Sua empresa está suspensa. Entre em contato com o administrador.']);
        }

        return $next($request);
    }
}