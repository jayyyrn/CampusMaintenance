<?php
namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;

class QueueController extends Controller
{
    public function index() {
        $queue = MaintenanceRequest::with(['teacher','department','activeAssignment.technician'])
            ->whereNotIn('status', ['completed','cancelled'])
            ->orderByRaw("FIELD(priority,'urgent','high','medium','low')")
            ->orderBy('date_reported','asc')
            ->get();

        $grouped = $queue->groupBy('category');

        return view('queue.index', compact('queue','grouped'));
    }

    public function display()
    {
        return view('queue.display');
    }

    public function displayData()
    {
        $queue = MaintenanceRequest::with(['teacher','department','activeAssignment.technician'])
            ->whereNotIn('status', ['completed','cancelled'])
            ->orderByRaw("FIELD(priority,'urgent','high','medium','low')")
            ->orderBy('date_reported','asc')
            ->get();

        $grouped = $queue->groupBy('category');

        $payload = [];

        foreach ($grouped as $category => $items) {
            $payload[] = [
                'category' => $category,
                'count'    => $items->count(),
                'items'    => $items->map(function ($r) {
                    return [
                        'request_id'      => $r->request_id,
                        'request_code'    => $r->request_code,
                        'title'           => $r->title,
                        'department'      => $r->department->dept_name ?? '—',
                        'location'        => $r->location ?? '—',
                        'teacher'         => $r->teacher->full_name ?? '—',
                        'priority'        => $r->priority,
                        'status'          => $r->status,
                        'status_label'    => $r->statusLabel(),
                        'queue_position'  => $r->queue_position,
                        'date_reported'   => $r->date_reported->toIso8601String(),
                        'waiting_minutes' => (int) $r->date_reported->diffInMinutes(now()),
                    ];
                })->values(),
            ];
        }

        return response()->json([
            'ok'         => true,
            'updated'    => now()->toIso8601String(),
            'total'      => $queue->count(),
            'categories' => $payload,
        ]);
    }

    public function displayDetail($id)
    {
        $req = MaintenanceRequest::with(['teacher','department'])
            ->whereNotIn('status', ['completed','cancelled'])
            ->find($id);

        if (!$req) {
            return response()->json(['ok' => false, 'error' => 'Request not found'], 404);
        }

        return response()->json([
            'ok' => true,
            'request' => [
                'request_id'      => $req->request_id,
                'request_code'    => $req->request_code,
                'title'           => $req->title,
                'description'     => $req->description,
                'department'      => $req->department->dept_name ?? '—',
                'location'        => $req->location ?? '—',
                'teacher'         => $req->teacher->full_name ?? '—',
                'priority'        => $req->priority,
                'status'          => $req->status,
                'status_label'    => $req->statusLabel(),
                'queue_position'  => $req->queue_position,
                'date_reported'   => optional($req->date_reported)->toIso8601String(),
                'waiting_minutes' => (int) $req->date_reported->diffInMinutes(now()),
            ],
        ]);
    }
}