<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Tenant::class);

        $tenants = Tenant::withCount(['users'])
            ->with(['users' => function($query) {
                $query->where('role', 'tenant_admin')->limit(1);
            }])
            ->latest()
            ->paginate(20);

        return view('admin.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Tenant::class);

        return view('admin.tenants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Criar tenant
        $tenant = Tenant::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'plan' => $validated['plan'],
            'max_users' => $validated['max_users'],
            'max_expenses' => $validated['max_expenses'],
            'status' => 'active',
            'trial_ends_at' => now()->addDays(30),
            'subscription_ends_at' => now()->addYear(),
        ]);

        // Criar admin do tenant
        User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => bcrypt($validated['admin_password']),
            'tenant_id' => $tenant->id,
            'role' => 'tenant_admin',
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.tenants.index')
            ->with('success', 'Empresa criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant): View
    {
        $this->authorize('view', $tenant);

        $tenant->load(['users']);
        
        $stats = [
            'total_users' => $tenant->users()->count(),
            'total_expenses' => $tenant->expenses()->count(),
            'total_categories' => $tenant->categories()->count(),
            'total_payment_methods' => $tenant->paymentMethods()->count(),
            'total_spent' => $tenant->expenses()->sum('amount'),
        ];

        return view('admin.tenants.show', compact('tenant', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant): View
    {
        $this->authorize('update', $tenant);

        return view('admin.tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $tenant->update($request->validated());

        return redirect()
            ->route('admin.tenants.index')
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        $this->authorize('delete', $tenant);

        $tenantName = $tenant->name;
        
        // Contar antes de excluir
        $stats = [
            'users' => $tenant->users()->count(),
            'expenses' => $tenant->expenses()->count(),
            'categories' => $tenant->categories()->count(),
            'payment_methods' => $tenant->paymentMethods()->count(),
        ];

        // Pegar IDs dos usuários
        $userIds = $tenant->users()->pluck('id')->toArray();
        
        // Excluir TUDO em cascata
        if (!empty($userIds)) {
            \App\Models\Expense::whereIn('user_id', $userIds)->delete();
            \App\Models\Category::whereIn('user_id', $userIds)->delete();
            \App\Models\PaymentMethod::whereIn('user_id', $userIds)->delete();
        }
        
        // Excluir usuários e tenant
        $tenant->users()->delete();
        $tenant->delete();

        return redirect()
            ->route('admin.tenants.index')
            ->with('success', "Empresa '{$tenantName}' excluído! Removidos: {$stats['users']} usuários, {$stats['expenses']} despesas, {$stats['categories']} categorias, {$stats['payment_methods']} métodos.");
    }

    /**
     * Suspend the specified tenant.
     */
    public function suspend(Tenant $tenant): RedirectResponse
    {
        $this->authorize('suspend', $tenant);

        // Suspender o tenant
        $tenant->update(['status' => 'suspended']);

        // Suspender TODOS os usuários do tenant
        $tenant->users()->update(['is_active' => false]);

        return back()->with('success', 'Empresa e todos os ' . $tenant->users()->count() . ' usuários foram suspensos!');
    }

    /**
     * Activate the specified tenant.
     */
    public function activate(Tenant $tenant): RedirectResponse
    {
        $this->authorize('activate', $tenant);

        // Ativar o tenant
        $tenant->update(['status' => 'active']);

        // Reativar TODOS os usuários do tenant
        $tenant->users()->update(['is_active' => true]);

        return back()->with('success', 'Empresa e todos os ' . $tenant->users()->count() . ' usuários foram reativados!');
    }
}