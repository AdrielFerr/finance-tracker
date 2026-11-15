<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\StatementImportController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SystemConfigController;
use App\Http\Controllers\Admin\WhatsAppConfigController;
use App\Models\User;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
    Route::post('/dashboard/update-overdue', [DashboardController::class, 'updateOverdue'])->name('dashboard.update-overdue');

    // Notificacoes internas
    Route::get("notifications/{notification}", [NotificationController::class, "open"])->name("notifications.open");
    Route::post("notifications/read-all", [NotificationController::class, "markAllAsRead"])->name("notifications.read-all");

    // Expenses (Despesas)
    Route::delete('expenses/bulk-delete', [ExpenseController::class, 'bulkDelete'])->name('expenses.bulk-delete'); // ← ANTES!
    Route::post("expenses/import", [StatementImportController::class, "store"])->name("expenses.import.store");
    Route::get("expenses/import/{import}", [StatementImportController::class, "show"])->name("expenses.import.show");
    Route::post("expenses/import/{import}/confirm", [StatementImportController::class, "confirm"])->name("expenses.import.confirm");
    Route::delete("expenses/import/{import}", [StatementImportController::class, "destroy"])->name("expenses.import.destroy");
    Route::resource("expenses", ExpenseController::class);
    Route::post('expenses/{expense}/mark-paid', [ExpenseController::class, 'markAsPaid'])->name('expenses.mark-paid');
    Route::post('expenses/{expense}/duplicate', [ExpenseController::class, 'duplicate'])->name('expenses.duplicate');
    Route::get('expenses/{expense}/download-receipt', [ExpenseController::class, 'downloadReceipt'])->name('expenses.download-receipt');

    // Categories (Categorias)
    Route::resource('categories', CategoryController::class);
    Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

    // Payment Methods (Métodos de Pagamento)
    Route::resource('payment-methods', PaymentMethodController::class);
    Route::post('payment-methods/{paymentMethod}/toggle-status', [PaymentMethodController::class, 'toggleStatus'])->name('payment-methods.toggle-status');

    // Reports (Relatórios)
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('reports/annual', [ReportController::class, 'annual'])->name('reports.annual');
    Route::get('reports/comparative', [ReportController::class, 'comparative'])->name('reports.comparative');
    Route::get('reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::get('reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
});

Route::middleware('auth')->group(function () {
    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::put('/profile/notifications', [ProfileController::class, 'updateNotifications'])->name('profile.update-notifications');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.remove-avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::resource('tenants', TenantController::class);
        Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend'])
            ->name('tenants.suspend');
        Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate'])
            ->name('tenants.activate');
});

Route::middleware(['auth', 'verified', 'super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
});

Route::middleware(['auth', 'verified', 'super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/system-config', [SystemConfigController::class, 'index'])
            ->name('system-config.index');
        Route::put('/system-config', [SystemConfigController::class, 'update'])
            ->name('system-config.update');
        Route::put('/system-config/whatsapp', [WhatsAppConfigController::class, 'update'])
            ->name('system-config.whatsapp.update');
        Route::delete('/system-config/whatsapp/key', [WhatsAppConfigController::class, 'forgetKey'])
            ->name('system-config.whatsapp.forget-key');
        Route::post('/system-config/whatsapp/test', [WhatsAppConfigController::class, 'test'])
            ->name('system-config.whatsapp.test');
});

require __DIR__ . '/auth.php';
