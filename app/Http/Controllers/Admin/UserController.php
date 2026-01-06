<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $currentUser = Auth::user(); 
        
        $users = User::with('tenant')
            ->when($currentUser->role !== 'super_admin', function ($query) use ($currentUser) {
                $query->where('tenant_id', $currentUser->tenant_id);
            })
            ->latest()
            ->paginate(20);

        $tenants = Tenant::where('status', 'active')->get();

        return view('admin.users.index', compact('users', 'tenants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'tenant_id' => ['required', 'exists:tenants,id'],
            'role' => ['required', Rule::in(['tenant_admin', 'user'])],
            'is_active' => ['boolean'],
        ]);

        // Verificar limite de usuários do tenant
        $tenant = Tenant::findOrFail($validated['tenant_id']);
        if ($tenant->users()->count() >= $tenant->max_users) {
            return back()->withErrors([
                'tenant_id' => "Limite de usuários atingido para este tenant ({$tenant->max_users} usuários)."
            ])->withInput();
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');

        User::create($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'tenant_id' => ['required', 'exists:tenants,id'],
            'role' => ['required', Rule::in(['tenant_admin', 'user'])],
            'is_active' => ['boolean'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active');

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Não permitir excluir super admin
        if ($user->role === 'super_admin') {
            return back()->withErrors(['error' => 'Não é possível excluir um Super Admin!']);
        }

        // Não permitir excluir a si mesmo
        if ($user->id === Auth::id()) {  // ← Auth::id()
            return back()->withErrors(['error' => 'Você não pode excluir seu próprio usuário!']);
        }

        $userName = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Usuário '{$userName}' excluído com sucesso!");
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        // Não permitir desativar super admin
        if ($user->role === 'super_admin') {
            return back()->withErrors(['error' => 'Não é possível desativar um Super Admin!']);
        }

        // Não permitir desativar a si mesmo
        if ($user->id === Auth::id()) {  // ← Auth::id()
            return back()->withErrors(['error' => 'Você não pode desativar seu próprio usuário!']);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'ativado' : 'desativado';
        
        return back()->with('success', "Usuário {$status} com sucesso!");
    }
}