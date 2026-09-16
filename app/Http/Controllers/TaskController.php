<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{TaskAssignment, Diagnosis, User};

class TaskController extends Controller
{
    public function index(Request $request) {
        $user = $request->user();

        $tasks = TaskAssignment::with(['request.teacher','request.department'])
            ->where('technician_id', $user->user_id)
            ->orderByRaw("FIELD(status,'in_progress','pending','for_review','completed')")
            ->orderByDesc('assigned_at')
            ->get();

        // Group by status (project management board)
        $board = [
            'pending'     => $tasks->where('status','pending'),
            'in_progress' => $tasks->where('status','in_progress'),
            'for_review'  => $tasks->where('status','for_review'),
            'completed'   => $tasks->where('status','completed'),
        ];

        return view('tasks.index', compact('board'));
    }

    public function show($id) {
        $task = TaskAssignment::with(['request.teacher','request.equipment','request.diagnoses'])
            ->findOrFail($id);
        return view('tasks.show', compact('task'));
    }

    public function update(Request $request, $id) {
        $task = TaskAssignment::where('assignment_id', $id)
            ->where('technician_id', $request->user()->user_id)
            ->firstOrFail();

        $request->validate([
            'status' => 'required|in:pending,in_progress,for_review,completed',
            'notes'  => 'nullable|string',
        ]);

        $data = ['status' => $request->status, 'notes' => $request->notes];
        if ($request->status === 'in_progress' && !$task->started_at) $data['started_at'] = now();
        if ($request->status === 'completed') $data['completed_at'] = now();

        $task->update($data);

        // Sync request status
        $map = [
            'in_progress' => 'in_progress',
            'for_review'  => 'for_verification',
            'completed'   => 'completed',
        ];
        if (isset($map[$request->status])) {
            $task->request->update([
                'status'         => $map[$request->status],
                'date_completed' => $request->status === 'completed' ? now() : $task->request->date_completed,
            ]);

            notify($task->request->teacher_id, "Task Update",
                "{$task->request->request_code}: ".ucwords(str_replace('_',' ',$request->status)),
                'info', route('requests.show', $task->request_id));
        }

        audit('UPDATE_TASK', 'task', $id, $request->status);
        return back()->with('success', 'Task updated.');
    }

    public function saveDiagnosis(Request $request, $id) {
        $task = TaskAssignment::where('assignment_id', $id)
            ->where('technician_id', $request->user()->user_id)
            ->firstOrFail();

        $data = $request->validate([
            'findings'           => 'required|string',
            'recommended_action' => 'nullable|string',
            'materials_needed'   => 'nullable|string',
            'diagnosis_result'   => 'nullable|string',
            'solution_steps'     => 'nullable|string',
        ]);

        Diagnosis::create([
            'request_id'         => $task->request_id,
            'technician_id'      => $request->user()->user_id,
            'findings'           => $data['findings'],
            'recommended_action' => $data['recommended_action'] ?? null,
            'materials_needed'   => $data['materials_needed'] ?? null,
            'diagnosis_result'   => $data['diagnosis_result'] ?? null,
            'solution_steps'     => $data['solution_steps'] ?? null,
        ]);

        notify($task->request->teacher_id, "Diagnosis Recorded",
            "A diagnosis was recorded for {$task->request->request_code}.",
            'info', route('requests.show', $task->request_id));

        audit('ADD_DIAGNOSIS', 'task', $id);
        return back()->with('success', 'Diagnosis saved. It will be added to the AI knowledge base.');
    }
}