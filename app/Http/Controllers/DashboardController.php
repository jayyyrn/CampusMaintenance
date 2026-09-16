<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{MaintenanceRequest, TaskAssignment, Inventory, MaterialRequest};

class DashboardController extends Controller
{
    public function index(Request $request) {
        $user = $request->user();
        $data = [];

        if ($user->isTeacher()) {
            $data['stats'] = [
                'total'     => MaintenanceRequest::where('teacher_id', $user->user_id)->count(),
                'pending'   => MaintenanceRequest::where('teacher_id', $user->user_id)
                                ->whereIn('status', ['pending','review','assigned','in_progress','for_verification'])->count(),
                'completed' => MaintenanceRequest::where('teacher_id', $user->user_id)->where('status','completed')->count(),
            ];
            $data['recent'] = MaintenanceRequest::with('activeAssignment.technician')
                ->where('teacher_id', $user->user_id)
                ->latest('date_reported')->limit(5)->get();

        } elseif ($user->isTechnician()) {
            $data['stats'] = [
                'pending'     => TaskAssignment::where('technician_id', $user->user_id)->where('status','pending')->count(),
                'in_progress' => TaskAssignment::where('technician_id', $user->user_id)->where('status','in_progress')->count(),
                'for_review'  => TaskAssignment::where('technician_id', $user->user_id)->where('status','for_review')->count(),
                'completed'   => TaskAssignment::where('technician_id', $user->user_id)->where('status','completed')->count(),
            ];
            $data['tasks'] = TaskAssignment::with('request')
                ->where('technician_id', $user->user_id)
                ->whereIn('status', ['pending','in_progress','for_review'])
                ->orderByRaw("FIELD(request_id, (SELECT request_id FROM maintenance_requests WHERE maintenance_requests.request_id = task_assignments.request_id ORDER BY FIELD(priority,'urgent','high','medium','low')))")
                ->limit(10)->get();

        } elseif ($user->isInventoryOfficer()) {
            $data['stats'] = [
                'total_items'       => Inventory::count(),
                'low_stock'         => Inventory::whereColumn('qty_on_hand','<=','min_stock_level')->count(),
                'pending_requests'  => MaterialRequest::where('status','pending')->count(),
            ];
            $data['low_stock'] = Inventory::whereColumn('qty_on_hand','<=','min_stock_level')->limit(10)->get();
            $data['pending_reqs'] = MaterialRequest::with(['item','technician'])
                ->where('status','pending')->latest()->limit(10)->get();

        } else {
            // admin / coordinator
            $data['stats'] = [
                'total_requests'  => MaintenanceRequest::count(),
                'active_requests' => MaintenanceRequest::whereNotIn('status',['completed','cancelled'])->count(),
                'completed'       => MaintenanceRequest::where('status','completed')->count(),
                'low_stock'       => Inventory::whereColumn('qty_on_hand','<=','min_stock_level')->count(),
            ];
            $data['recent'] = MaintenanceRequest::with(['teacher','department'])
                ->latest('date_reported')->limit(10)->get();
        }

        return view('dashboard', compact('data'));
    }
}