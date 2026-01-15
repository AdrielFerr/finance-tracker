<?php

namespace App\Http\Controllers;

use App\Models\LeadPipeline;
use App\Models\LeadStage;
use App\Repositories\LeadPipelineRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LeadPipelineController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct(
        private LeadPipelineRepository $pipelineRepository
    ) {}

    /**
     * Lista de pipelines
     */
    public function index()
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        $pipelines = $this->pipelineRepository->getForTenant($tenant, $user);

        return view('leads.pipelines.index', compact('pipelines'));
    }

    /**
     * Visualizar pipeline com estatísticas
     */
    public function show(LeadPipeline $pipeline)
    {
        // Super admin não precisa de authorization
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('view', $pipeline);
        }

        $stats = $this->pipelineRepository->getStats($pipeline);

        return view('leads.pipelines.show', compact('pipeline', 'stats'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        return view('leads.pipelines.create');
    }

    /**
     * Salvar novo pipeline
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'is_default' => 'sometimes|boolean',
        ]);

        $user = Auth::user();
        $tenant = $user->tenant;

        // Converter checkbox para boolean
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['is_default'] = $request->has('is_default') ? true : false;

        $pipeline = $this->pipelineRepository->create($tenant, $validated, $user);

        return redirect()
            ->route('leads.pipelines.edit', $pipeline->id)
            ->with('success', 'Pipeline criado com sucesso! Agora adicione os estágios.');
    }

    /**
     * Formulário de edição
     */
    public function edit(LeadPipeline $pipeline)
    {
        // Super admin não precisa de authorization
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('update', $pipeline);
        }

        return view('leads.pipelines.edit', compact('pipeline'));
    }

    /**
     * Atualizar pipeline
     */
    public function update(Request $request, LeadPipeline $pipeline)
    {
        // Super admin não precisa de authorization
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('update', $pipeline);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'is_default' => 'sometimes|boolean',
        ]);

        // Converter checkbox para boolean
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['is_default'] = $request->has('is_default') ? true : false;

        $pipeline = $this->pipelineRepository->update($pipeline, $validated);

        return back()->with('success', 'Pipeline atualizado com sucesso!');
    }

    /**
     * Deletar pipeline
     */
    public function destroy(LeadPipeline $pipeline)
    {
        // Super admin não precisa de authorization
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('delete', $pipeline);
        }

        // Não pode deletar pipeline padrão
        if ($pipeline->is_default) {
            return back()->with('error', 'Não é possível deletar o pipeline padrão.');
        }

        $deleted = $this->pipelineRepository->delete($pipeline);

        if (!$deleted) {
            return back()->with('error', 'Não é possível deletar um pipeline com leads associados.');
        }

        return redirect()
            ->route('leads.pipelines.index')
            ->with('success', 'Pipeline removido com sucesso!');
    }

    /**
     * Duplicar pipeline
     */
    public function duplicate(LeadPipeline $pipeline)
    {
        // Super admin não precisa de authorization
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('view', $pipeline);
        }

        $user = Auth::user();
        $tenant = $user->tenant;

        $newPipeline = $this->pipelineRepository->duplicate($pipeline, $tenant, $user);

        return redirect()
            ->route('leads.pipelines.edit', $newPipeline->id)
            ->with('success', 'Pipeline duplicado com sucesso!');
    }

    // ==========================================
    // ESTÁGIOS
    // ==========================================

    /**
     * Criar estágio
     */
    public function storeStage(Request $request, LeadPipeline $pipeline)
    {
        // Super admin não precisa de authorization
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('update', $pipeline);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'order' => 'nullable|integer',
            'win_probability' => 'nullable|integer|min:0|max:100',
        ]);

        // Se não informou order, colocar como último
        if (!isset($validated['order'])) {
            $validated['order'] = $pipeline->stages()->max('order') + 1;
        }

        $stage = $this->pipelineRepository->createStage($pipeline, $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'stage' => $stage,
            ]);
        }

        return back()->with('success', 'Estágio criado com sucesso!');
    }

    /**
     * Atualizar estágio
     */
    public function updateStage(Request $request, LeadStage $stage)
    {
        // Super admin não precisa de authorization
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('update', $stage->pipeline);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'order' => 'sometimes|integer',
            'win_probability' => 'nullable|integer|min:0|max:100',
        ]);

        $stage = $this->pipelineRepository->updateStage($stage, $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'stage' => $stage,
            ]);
        }

        return back()->with('success', 'Estágio atualizado com sucesso!');
    }

    /**
     * Deletar estágio
     */
    public function destroyStage(LeadStage $stage)
    {
        // Super admin não precisa de authorization
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('update', $stage->pipeline);
        }

        $deleted = $this->pipelineRepository->deleteStage($stage);

        if (!$deleted) {
            return back()->with('error', 'Não é possível deletar um estágio com leads associados.');
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Estágio removido!',
            ]);
        }

        return back()->with('success', 'Estágio removido com sucesso!');
    }

    /**
     * Reordenar estágios (drag & drop)
     */
    public function reorderStages(Request $request, LeadPipeline $pipeline)
    {
        // Super admin não precisa de authorization
        if (Auth::user()->role !== 'super_admin') {
            $this->authorize('update', $pipeline);
        }

        $validated = $request->validate([
            'stages' => 'required|array',
            'stages.*' => 'exists:lead_stages,id',
        ]);

        $this->pipelineRepository->reorderStages($pipeline, $validated['stages']);

        return response()->json([
            'success' => true,
            'message' => 'Ordem atualizada!',
        ]);
    }
}
