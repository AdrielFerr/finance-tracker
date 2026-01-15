<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Repositories\LeadActivityRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LeadActivityController extends Controller
{
     use AuthorizesRequests;
     
    public function __construct(
        private LeadActivityRepository $activityRepository
    ) {}

    /**
     * Adicionar nota ao lead
     */
    public function addNote(Request $request, Lead $lead)
    {
        $this->authorize('view', $lead);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $user = Auth::user();
        $activity = $this->activityRepository->addNote($lead, $user, $validated['content']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'activity' => $activity->load('user'),
            ]);
        }

        return back()->with('success', 'Nota adicionada!');
    }

    /**
     * Registrar ligação
     */
    public function logCall(Request $request, Lead $lead)
    {
        $this->authorize('view', $lead);

        $validated = $request->validate([
            'description' => 'required|string',
            'duration' => 'nullable|string|max:20',
            'outcome' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $activity = $this->activityRepository->logCall(
            $lead,
            $user,
            $validated['description'],
            $validated['duration'] ?? null,
            $validated['outcome'] ?? null
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'activity' => $activity->load('user'),
            ]);
        }

        return back()->with('success', 'Ligação registrada!');
    }

    /**
     * Registrar reunião
     */
    public function logMeeting(Request $request, Lead $lead)
    {
        $this->authorize('view', $lead);

        $validated = $request->validate([
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'attendees' => 'nullable|array',
        ]);

        $user = Auth::user();
        $activity = $this->activityRepository->logMeeting(
            $lead,
            $user,
            $validated['description'],
            $validated['location'] ?? null,
            $validated['attendees'] ?? null
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'activity' => $activity->load('user'),
            ]);
        }

        return back()->with('success', 'Reunião registrada!');
    }

    /**
     * Agendar tarefa
     */
    public function scheduleTask(Request $request, Lead $lead)
    {
        $this->authorize('view', $lead);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_at' => 'required|date',
            'priority' => 'nullable|in:low,medium,high',
        ]);

        $user = Auth::user();
        $activity = $this->activityRepository->scheduleTask(
            $lead,
            $user,
            $validated['title'],
            $validated['description'],
            $validated['due_at'],
            $validated['priority'] ?? 'medium'
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'activity' => $activity->load('user'),
            ]);
        }

        return back()->with('success', 'Tarefa agendada!');
    }

    /**
     * Marcar tarefa como concluída
     */
    public function completeTask(LeadActivity $activity)
    {
        $this->authorize('update', $activity->lead);

        $activity = $this->activityRepository->completeTask($activity);

        return response()->json([
            'success' => true,
            'activity' => $activity,
        ]);
    }

    /**
     * Atualizar atividade
     */
    public function update(Request $request, LeadActivity $activity)
    {
        $this->authorize('update', $activity->lead);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'due_at' => 'sometimes|date',
            'priority' => 'sometimes|in:low,medium,high',
            'is_pinned' => 'sometimes|boolean',
        ]);

        $activity = $this->activityRepository->update($activity, $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'activity' => $activity->load('user'),
            ]);
        }

        return back()->with('success', 'Atividade atualizada!');
    }

    /**
     * Deletar atividade
     */
    public function destroy(LeadActivity $activity)
    {
        $this->authorize('update', $activity->lead);

        $this->activityRepository->delete($activity);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Atividade removida!',
            ]);
        }

        return back()->with('success', 'Atividade removida!');
    }

    /**
     * Buscar timeline do lead
     */
    public function timeline(Lead $lead)
    {
        $this->authorize('view', $lead);

        $timeline = $this->activityRepository->getTimeline($lead);

        return response()->json([
            'success' => true,
            'timeline' => $timeline,
        ]);
    }

    /**
     * Minhas tarefas
     */
    public function myTasks(Request $request)
    {
        $user = Auth::user();
        $completed = $request->input('completed');

        if ($completed !== null) {
            $completed = filter_var($completed, FILTER_VALIDATE_BOOLEAN);
        }

        $tasks = $this->activityRepository->getMyTasks($user, $completed);

        return view('leads.my-tasks', compact('tasks', 'completed'));
    }
}
