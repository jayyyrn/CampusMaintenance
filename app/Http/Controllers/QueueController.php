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

        // Group by category for easier tracking
        $grouped = $queue->groupBy('category');

        return view('queue.index', compact('queue','grouped'));
    }
}