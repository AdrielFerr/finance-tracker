<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Tenant;
use App\Repositories\LeadRepository;
use App\Repositories\LeadPipelineRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\LeadPipeline;
use App\Models\User;


class LeadController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private LeadRepository $leadRepository,
        private LeadPipelineRepository $pipelineRepository
    ) {}

    /**
     * Obter tenant (super admin sem tenant retorna null)
     */
    private function getTenant()
    {
        $user = Auth::user();
        
        // Super admin não tem tenant
        if ($user->role === 'super_admin') {
            return null;
        }
        
        // Usuário normal usa seu tenant
        if (!$user->tenant_id) {
            return null;
        }
        
        return Tenant::find($user->tenant_id);
    }

    /**
     * Lista de leads (com filtros)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        // Buscar pipelines
        $pipelines = LeadPipeline::where(function($q) use ($tenant, $user) {
            if ($tenant) {
                $q->where('tenant_id', $tenant->id);
            } else {
                $q->where('created_by', $user->id);
            }
        })
        ->where('is_active', true)
        ->with('stages')
        ->orderBy('order')
        ->get();

        // Pipeline selecionado (para Kanban)
        $selectedPipeline = null;
        if ($request->filled('pipeline_id')) {
            $selectedPipeline = $pipelines->firstWhere('id', $request->pipeline_id);
        }
        
        if (!$selectedPipeline) {
            $selectedPipeline = $pipelines->firstWhere('is_default', true) ?? $pipelines->first();
        }

        // Query base de leads
        $query = Lead::with(['stage', 'pipeline', 'assignedTo'])
            ->where(function($q) use ($tenant, $user) {
                if ($tenant) {
                    $q->where('tenant_id', $tenant->id);
                } else {
                    $q->where('created_by', $user->id);
                }
            });

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('pipeline_id')) {
            $query->where('pipeline_id', $request->pipeline_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Leads para lista (paginado)
        $leads = $query->orderBy('created_at', 'desc')->paginate(15);

        // Leads para Kanban (agrupado por estágio)
        $leadsByStage = collect();
        if ($selectedPipeline) {
            $kanbanLeads = Lead::with(['stage', 'assignedTo'])
                ->where('pipeline_id', $selectedPipeline->id)
                ->where('status', 'open')
                ->where(function($q) use ($tenant, $user) {
                    if ($tenant) {
                        $q->where('tenant_id', $tenant->id);
                    } else {
                        $q->where('created_by', $user->id);
                    }
                })
                ->orderBy('order')
                ->get();
            
            $leadsByStage = $kanbanLeads->groupBy('stage_id');
        }

        // Usuários (para filtro)
        $users = User::where(function($q) use ($tenant, $user) {
            if ($tenant) {
                $q->where('tenant_id', $tenant->id);
            } else {
                $q->where('id', $user->id);
            }
        })->get();

        // Estatísticas
        $stats = [
            'total_leads' => Lead::where(function($q) use ($tenant, $user) {
                if ($tenant) {
                    $q->where('tenant_id', $tenant->id);
                } else {
                    $q->where('created_by', $user->id);
                }
            })->count(),
            
            'open_leads' => Lead::where('status', 'open')
                ->where(function($q) use ($tenant, $user) {
                    if ($tenant) {
                        $q->where('tenant_id', $tenant->id);
                    } else {
                        $q->where('created_by', $user->id);
                    }
                })->count(),
            
            'won_leads' => Lead::where('status', 'won')
                ->where(function($q) use ($tenant, $user) {
                    if ($tenant) {
                        $q->where('tenant_id', $tenant->id);
                    } else {
                        $q->where('created_by', $user->id);
                    }
                })->count(),
            
            'total_value' => Lead::where('status', 'open')
                ->where(function($q) use ($tenant, $user) {
                    if ($tenant) {
                        $q->where('tenant_id', $tenant->id);
                    } else {
                        $q->where('created_by', $user->id);
                    }
                })->sum('value'),
        ];

        return view('leads.index', compact(
            'leads',
            'pipelines',
            'selectedPipeline',
            'leadsByStage',
            'users',
            'stats'
        ));
    }

    /**
     * View Kanban
     */
    public function kanban(Request $request)
    {
        $user = Auth::user();
        $tenant = $this->getTenant();

        // Pipeline selecionado ou padrão
        $pipelineId = $request->input('pipeline_id');
        if (!$pipelineId) {
            $defaultPipeline = $this->pipelineRepository->getDefault($tenant, $user);
            $pipelineId = $defaultPipeline?->id;
        }

        if (!$pipelineId) {
            return redirect()->route('leads.index')
                ->with('error', 'Nenhum pipeline encontrado. Execute: php artisan db:seed --class=LeadPipelineSeeder');
        }

        $pipeline = $this->pipelineRepository->find($pipelineId);
        $leadsByStage = $this->leadRepository->getForKanban($tenant, $pipelineId, $user); // ← Passar usuário
        $pipelines = $this->pipelineRepository->getForTenant($tenant, $user);
        $stats = $this->leadRepository->getStats($tenant, $pipelineId, $user); // ← Passar usuário

        return view('leads.kanban', compact('pipeline', 'leadsByStage', 'pipelines', 'stats'));
    }

    /**
     * Formulário de criação
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $tenant = $this->getTenant();

        $pipelines = $this->pipelineRepository->getForTenant($tenant, $user);
        $pipelineId = $request->input('pipeline_id') ?? $pipelines->first()?->id;
        
        $pipeline = null;
        if ($pipelineId) {
            $pipeline = $this->pipelineRepository->find($pipelineId);
        }

        // Usuários para atribuição
        $users = $tenant ? \App\Models\User::where('tenant_id', $tenant->id)->get() : collect([$user]);

        return view('leads.create', compact('pipelines', 'pipeline', 'users'));
    }

    /**
     * Salvar novo lead
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pipeline_id' => 'required|exists:lead_pipelines,id',
            'stage_id' => 'nullable|exists:lead_stages,id',
            'title' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_position' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'company_size' => 'nullable|string|max:50',
            'company_address' => 'nullable|string',
            'description' => 'nullable|string',
            'value' => 'nullable|numeric|min:0',
            'source' => 'nullable|in:website,referral,social_media,email_campaign,cold_call,event,partner,organic_search,paid_ads,other',
            'source_details' => 'nullable|string|max:255',
            'expected_close_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'probability' => 'nullable|integer|min:0|max:100',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $user = Auth::user();
        $tenant = $this->getTenant();

        $lead = $this->leadRepository->create($tenant, $user, $validated);

        return redirect()
            ->route('leads.show', $lead->id)
            ->with('success', 'Lead criado com sucesso!');
    }

    /**
     * Visualizar lead
     */
    public function show(Lead $lead)
    {
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('view', $lead);
        }

        $lead = $this->leadRepository->findWithRelations($lead->id);
        return view('leads.show', compact('lead'));
    }

    /**
     * Formulário de edição
     */
    public function edit(Lead $lead)
    {
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('update', $lead);
        }

        $user = Auth::user();
        $tenant = $this->getTenant();

        $pipelines = $this->pipelineRepository->getForTenant($tenant, $user);
        $pipeline = $lead->pipeline;
        $users = $tenant ? \App\Models\User::where('tenant_id', $tenant->id)->get() : collect([$user]);

        return view('leads.edit', compact('lead', 'pipelines', 'pipeline', 'users'));
    }

    /**
     * Atualizar lead
     */
    public function update(Request $request, Lead $lead)
    {
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('update', $lead);
        }

        $validated = $request->validate([
            'pipeline_id' => 'sometimes|exists:lead_pipelines,id',
            'stage_id' => 'sometimes|exists:lead_stages,id',
            'title' => 'sometimes|string|max:255',
            'contact_name' => 'sometimes|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_position' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'company_size' => 'nullable|string|max:50',
            'company_address' => 'nullable|string',
            'description' => 'nullable|string',
            'value' => 'nullable|numeric|min:0',
            'source' => 'nullable|in:website,referral,social_media,email_campaign,cold_call,event,partner,organic_search,paid_ads,other',
            'source_details' => 'nullable|string|max:255',
            'expected_close_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'probability' => 'nullable|integer|min:0|max:100',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $user = Auth::user();
        $lead = $this->leadRepository->update($lead, $user, $validated);

        return redirect()
            ->route('leads.show', $lead->id)
            ->with('success', 'Lead atualizado com sucesso!');
    }

    /**
     * Deletar lead
     */
    public function destroy(Lead $lead)
    {
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('delete', $lead);
        }

        $this->leadRepository->delete($lead);

        return redirect()
            ->route('leads.index')
            ->with('success', 'Lead removido com sucesso!');
    }

    /**
     * Mover lead para outro estágio (AJAX)
     */
    public function moveStage(Request $request, Lead $lead)
    {
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('update', $lead);
        }

        $validated = $request->validate([
            'stage_id' => 'required|exists:lead_stages,id',
            'order' => 'nullable|integer',
        ]);

        $stage = \App\Models\LeadStage::find($validated['stage_id']);
        $user = Auth::user();

        $this->leadRepository->moveToStage($lead, $stage, $user, $validated['order'] ?? 0);

        return response()->json([
            'success' => true,
            'message' => 'Lead movido com sucesso!',
            'lead' => $lead->fresh(['stage', 'pipeline']),
        ]);
    }

    /**
     * Atualizar ordem dos leads (drag & drop)
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'leads' => 'required|array',
            'leads.*' => 'exists:leads,id',
        ]);

        $this->leadRepository->updateOrder($validated['leads']);

        return response()->json([
            'success' => true,
            'message' => 'Ordem atualizada!',
        ]);
    }

    /**
     * Marcar como ganho
     */
    public function markAsWon(Request $request, Lead $lead)
    {
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('update', $lead);
        }

        $user = Auth::user();
        $note = $request->input('note');
        $lead->markAsWon($user, $note);
        return back()->with('success', 'Lead marcado como ganho! 🎉');
    }

    /**
     * Marcar como perdido
     */
    public function markAsLost(Request $request, Lead $lead)
    {
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('update', $lead);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $lead->markAsLost($user, $validated['reason']);
        return back()->with('info', 'Lead marcado como perdido.');
    }

    /**
     * Reabrir lead
     */
    public function reopen(Lead $lead)
    {
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('update', $lead);
        }

        $user = Auth::user();
        $lead->reopen($user);
        return back()->with('success', 'Lead reaberto!');
    }

    /**
     * Duplicar lead
     */
    public function duplicate(Lead $lead)
    {
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('view', $lead);
        }

        $user = Auth::user();
        $newLead = $this->leadRepository->duplicate($lead, $user);

        return redirect()
            ->route('leads.show', $newLead->id)
            ->with('success', 'Lead duplicado com sucesso!');
    }

    /**
     * Meus leads
     */
    public function myLeads(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status');
        $leads = $this->leadRepository->getMyLeads($user, $status);
        return view('leads.my-leads', compact('leads', 'status'));
    }

    /**
     * Leads vencendo
     */
    public function upcoming(Request $request)
    {
        $user = Auth::user();
        $tenant = $this->getTenant();

        $days = $request->input('days', 7);
        $leads = $this->leadRepository->getUpcoming($tenant, $days, $user); // ← Passar usuário
        return view('leads.upcoming', compact('leads', 'days'));
    }

    /**
     * Leads atrasados
     */
    public function overdue()
    {
        $user = Auth::user();
        $tenant = $this->getTenant();

        $leads = $this->leadRepository->getOverdue($tenant, $user); // ← Passar usuário
        return view('leads.overdue', compact('leads'));
    }
}
