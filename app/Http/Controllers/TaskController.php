<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{TaskAssignment, Diagnosis, Inventory, User};

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = TaskAssignment::with(['request.teacher','request.department'])
            ->where('technician_id', $request->user()->user_id)
            ->orderByRaw("CASE status 
                            WHEN 'in_progress' THEN 1 
                            WHEN 'pending'     THEN 2 
                            WHEN 'for_review'  THEN 3 
                            WHEN 'completed'   THEN 4 
                            ELSE 5 END")
            ->orderByDesc('assigned_at')
            ->get();

        $board = [
            'pending'     => $tasks->where('status', 'pending'),
            'in_progress' => $tasks->where('status', 'in_progress'),
            'for_review'  => $tasks->where('status', 'for_review'),
            'completed'   => $tasks->where('status', 'completed'),
        ];

        return view('tasks.index', compact('board'));
    }

    public function show(Request $request, $id)
    {
        $task = TaskAssignment::with([
            'request.teacher',
            'request.equipment',
            'request.diagnoses',
            'request.materialRequests.item',
        ])->where('technician_id', $request->user()->user_id)
          ->findOrFail($id);

        $items = Inventory::orderBy('item_name')->get();

        return view('tasks.show', compact('task', 'items'));
    }

    public function update(Request $request, $id)
    {
        $task = TaskAssignment::where('assignment_id', $id)
            ->where('technician_id', $request->user()->user_id)
            ->firstOrFail();

        $request->validate([
            'status'      => 'required|in:pending,in_progress,for_review,completed',
            'notes'       => 'nullable|string|max:2000',
            'photo_after' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        DB::beginTransaction();
        try {
            $photoPath = $task->request->photo_after;

            if ($request->hasFile('photo_after')) {
                $photoPath = $request->file('photo_after')->store('requests', 'public');
            }

            $data = [
                'status' => $request->status,
                'notes'  => $request->notes ?? $task->notes,
            ];
            if ($request->status === 'in_progress' && !$task->started_at) {
                $data['started_at'] = now();
            }
            if ($request->status === 'completed') {
                $data['completed_at'] = now();
            }

            $task->update($data);

            $map = [
                'in_progress' => 'in_progress',
                'for_review'  => 'for_verification',
                'completed'   => 'completed',
            ];

            if (isset($map[$request->status])) {
                $update = ['status' => $map[$request->status]];

                if ($request->status === 'completed') {
                    $update['photo_after'] = $photoPath;

                    // Only mark request completed if ALL its tasks are done
                    $remaining = TaskAssignment::where('request_id', $task->request_id)
                        ->where('assignment_id', '!=', $task->assignment_id)
                        ->whereIn('status', ['pending','in_progress','for_review'])
                        ->count();

                    if ($remaining > 0) {
                        $update['status'] = 'in_progress';
                    } else {
                        $update['date_completed'] = now();
                    }
                }

                $task->request->update($update);
                update_queue_positions();

                notify($task->request->teacher_id, "Task Update",
                    "{$task->request->request_code}: " . ucwords(str_replace('_', ' ', $request->status)),
                    'info', route('requests.show', $task->request_id));
            }

            audit('UPDATE_TASK', 'task', $id, $request->status);

            DB::commit();
            return back()->with('success', 'Task updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Task update failed: ' . $e->getMessage());
            return back()->with('error', 'Update failed. Please try again.');
        }
    }

    public function saveDiagnosis(Request $request, $id)
    {
        $task = TaskAssignment::where('assignment_id', $id)
            ->where('technician_id', $request->user()->user_id)
            ->firstOrFail();

        $data = $request->validate([
            'findings'           => 'required|string|min:5',
            'recommended_action' => 'nullable|string',
            'materials_needed'   => 'nullable|string',
            'diagnosis_result'   => 'nullable|string',
            'solution_steps'     => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Update existing unverified diagnosis for same tech + request if exists
            $existing = Diagnosis::where('request_id', $task->request_id)
                ->where('technician_id', $request->user()->user_id)
                ->where('is_verified', false)
                ->latest()
                ->first();

            if ($existing) {
                $existing->update($data);
                $diagId = $existing->diagnosis_id;
            } else {
                $diag = Diagnosis::create([
                    'request_id'    => $task->request_id,
                    'technician_id' => $request->user()->user_id,
                    'findings'      => $data['findings'],
                    'recommended_action' => $data['recommended_action'] ?? null,
                    'materials_needed'   => $data['materials_needed'] ?? null,
                    'diagnosis_result'   => $data['diagnosis_result'] ?? null,
                    'solution_steps'     => $data['solution_steps'] ?? null,
                ]);
                $diagId = $diag->diagnosis_id;
            }

            notify($task->request->teacher_id, "Diagnosis Recorded",
                "A diagnosis was recorded for {$task->request->request_code}.",
                'info', route('requests.show', $task->request_id));

            foreach (User::whereIn('role', ['lead_technician','admin'])->get() as $sup) {
                notify($sup->user_id, "Diagnosis Needs Verification",
                    "A new diagnosis was submitted for {$task->request->request_code}.",
                    'warning', route('requests.show', $task->request_id));
            }

            audit('ADD_DIAGNOSIS', 'diagnosis', $diagId);

            DB::commit();
            return back()->with('success', 'Diagnosis saved and added to the AI knowledge base.');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Diagnosis save failed: ' . $e->getMessage());
            return back()->with('error', 'Could not save diagnosis. Please try again.');
        }
    }
}