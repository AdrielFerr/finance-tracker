<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantScope
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Super Admin vê tudo
        if ($user && $user->role === 'super_admin') {
            return $next($request);
        }

        // Usuários normais só veem dados do próprio tenant
        if ($user && $user->tenant_id) {
            // Aplicar scope global para isolar dados
            app()->instance('tenant_id', $user->tenant_id);
        }

        return $next($request);
    }
}