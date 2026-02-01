<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SystemConfigController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadActivityController;
use App\Http\Controllers\LeadPipelineController;
use App\Models\User;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
    Route::post('/dashboard/update-overdue', [DashboardController::class, 'updateOverdue'])->name('dashboard.update-overdue');

    // Expenses (Despesas)
    Route::delete('expenses/bulk-delete', [ExpenseController::class, 'bulkDelete'])->name('expenses.bulk-delete'); // ← ANTES!
    Route::resource('expenses', ExpenseController::class);
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
});

// ==========================================
// ROTAS DE LEADS
// ==========================================

Route::middleware(['auth'])->prefix('leads')->name('leads.')->group(function () {
    
    // ⭐ IMPORTANTE: Rotas específicas PRIMEIRO!
    Route::get('/', [LeadController::class, 'index'])->name('index');
    Route::get('/create', [LeadController::class, 'create'])->name('create');
    
    
    Route::prefix('pipelines')->name('pipelines.')->group(function () {
        Route::get('/', [LeadPipelineController::class, 'index'])->name('index');
        Route::get('/create', [LeadPipelineController::class, 'create'])->name('create');
        Route::post('/', [LeadPipelineController::class, 'store'])->name('store');
        Route::get('/{pipeline}', [LeadPipelineController::class, 'show'])->name('show');
        Route::get('/{pipeline}/edit', [LeadPipelineController::class, 'edit'])->name('edit');
        Route::put('/{pipeline}', [LeadPipelineController::class, 'update'])->name('update');
        Route::delete('/{pipeline}', [LeadPipelineController::class, 'destroy'])->name('destroy');
        Route::post('/{pipeline}/duplicate', [LeadPipelineController::class, 'duplicate'])->name('duplicate');
        
        // Estágios
        Route::post('/{pipeline}/stages', [LeadPipelineController::class, 'storeStage'])->name('stages.store');
        Route::put('/stages/{stage}', [LeadPipelineController::class, 'updateStage'])->name('stages.update');
        Route::delete('/stages/{stage}', [LeadPipelineController::class, 'destroyStage'])->name('stages.destroy');
        Route::post('/{pipeline}/stages/reorder', [LeadPipelineController::class, 'reorderStages'])->name('stages.reorder');
    });

    
    
    // ⭐ ROTAS COM {lead} - DEPOIS DE TUDO!
    Route::get('/{lead}', [LeadController::class, 'show'])->name('show');
    Route::get('/{lead}/edit', [LeadController::class, 'edit'])->name('edit');
    Route::put('/{lead}', [LeadController::class, 'update'])->name('update');
    Route::delete('/{lead}', [LeadController::class, 'destroy'])->name('destroy');

    
    
    // Resto das rotas...
    Route::post('/', [LeadController::class, 'store'])->name('store');
    Route::post('/{lead}/move-stage', [LeadController::class, 'moveStage'])->name('move-stage');
    Route::post('/update-order', [LeadController::class, 'updateOrder'])->name('update-order');
    Route::post('/{lead}/mark-won', [LeadController::class, 'markAsWon'])->name('mark-won');
    Route::post('/{lead}/mark-lost', [LeadController::class, 'markAsLost'])->name('mark-lost');
    Route::post('/{lead}/reopen', [LeadController::class, 'reopen'])->name('reopen');
    Route::post('/{lead}/duplicate', [LeadController::class, 'duplicate'])->name('duplicate');
    
    // Filtros
    Route::get('/my/leads', [LeadController::class, 'myLeads'])->name('my-leads');
    Route::get('/my/upcoming', [LeadController::class, 'upcoming'])->name('upcoming');
    Route::get('/my/overdue', [LeadController::class, 'overdue'])->name('overdue');
    
    // Atividades
    Route::prefix('{lead}/activities')->name('activities.')->group(function () {
        Route::post('/note', [LeadActivityController::class, 'addNote'])->name('add-note');
        Route::post('/call', [LeadActivityController::class, 'logCall'])->name('log-call');
        Route::post('/meeting', [LeadActivityController::class, 'logMeeting'])->name('log-meeting');
        Route::post('/task', [LeadActivityController::class, 'scheduleTask'])->name('schedule-task');
        Route::get('/timeline', [LeadActivityController::class, 'timeline'])->name('timeline');
    });
    
    Route::put('/activities/{activity}', [LeadActivityController::class, 'update'])->name('activities.update');
    Route::delete('/activities/{activity}', [LeadActivityController::class, 'destroy'])->name('activities.destroy');
    Route::post('/activities/{activity}/complete', [LeadActivityController::class, 'completeTask'])->name('activities.complete');
    Route::get('/my/tasks', [LeadActivityController::class, 'myTasks'])->name('my-tasks');

    Route::get('/kanban', [LeadController::class, 'kanban'])->name('kanban');
    
});

// ==========================================
// API ROUTES (se necessário)
// ==========================================

Route::middleware(['auth:sanctum'])->prefix('api/leads')->name('api.leads.')->group(function () {
    
    // Buscar leads (JSON)
    Route::get('/', [LeadController::class, 'index']);
    Route::get('/kanban', [LeadController::class, 'kanban']);
    Route::get('/{lead}', [LeadController::class, 'show']);
    
    // Criar/Atualizar (JSON)
    Route::post('/', [LeadController::class, 'store']);
    Route::put('/{lead}', [LeadController::class, 'update']);
    
    // Movimentar
    Route::post('/{lead}/move-stage', [LeadController::class, 'moveStage']);
    Route::post('/update-order', [LeadController::class, 'updateOrder']);
    
    // Atividades
    Route::post('/{lead}/activities/note', [LeadActivityController::class, 'addNote']);
    Route::post('/{lead}/activities/call', [LeadActivityController::class, 'logCall']);
    Route::post('/{lead}/activities/task', [LeadActivityController::class, 'scheduleTask']);
    Route::get('/{lead}/activities/timeline', [LeadActivityController::class, 'timeline']);
});

require __DIR__ . '/auth.php';
