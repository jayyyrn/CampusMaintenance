<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{MaintenanceRequest, Equipment, TaskAssignment, User, Diagnosis};

class RequestController extends Controller
{
    public function index(Request $request) {
        $user = $request->user();
        $q = $request->get('q');
        $status = $request->get('status');
        $category = $request->get('category');

        $query = MaintenanceRequest::with(['teacher','department','activeAssignment.technician']);

        if ($user->isTeacher()) {
            $query->where('teacher_id', $user->user_id);
        } elseif ($user->isTechnician()) {
            $query->whereHas('assignments', fn($q) => $q->where('technician_id', $user->user_id));
        }

        if ($q) {
            $query->where(function($x) use ($q) {
                $x->where('request_code','like',"%$q%")
                  ->orWhere('title','like',"%$q%")
                  ->orWhere('location','like',"%$q%");
            });
        }
        if ($status)   $query->where('status', $status);
        if ($category) $query->where('category', $category);

        $requests = $query->orderByRaw("FIELD(priority,'urgent','high','medium','low')")
            ->orderByDesc('date_reported')->paginate(15);

        return view('requests.index', compact('requests','q','status','category'));
    }

    public function create() {
        $equipment = Equipment::orderBy('equipment_name')->get();
        return view('requests.create', compact('equipment'));
    }

    public function store(Request $request) {
        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'description'  => 'required|string',
            'category'     => 'required|in:electrical,carpentry,fabrication,aircon,plumbing,general',
            'location'     => 'nullable|string|max:100',
            'priority'     => 'required|in:low,medium,high,urgent',
            'equipment_id' => 'nullable|exists:equipment,equipment_id',
            'photo_before' => 'nullable|image|max:4096',
        ]);

        DB::beginTransaction();
        try {
            $photoPath = null;
            if ($request->hasFile('photo_before')) {
                $photoPath = $request->file('photo_before')->store('requests', 'public');
            }

            $req = MaintenanceRequest::create([
                'request_code'  => generate_request_code(),
                'teacher_id'    => $request->user()->user_id,
                'equipment_id'  => $data['equipment_id'] ?? null,
                'department_id' => $request->user()->department_id,
                'category'      => $data['category'],
                'title'         => $data['title'],
                'description'   => $data['description'],
                'location'      => $data['location'] ?? null,
                'priority'      => $data['priority'],
                'photo_before'  => $photoPath,
            ]);

            update_queue_positions();

            // Notify supervisors
            foreach (User::whereIn('role',['coordinator','lead_technician','admin'])->where('status','active')->get() as $a) {
                notify($a->user_id, "New Request: {$req->request_code}", $req->title, 'info', route('requests.show', $req->request_id));
            }

            audit('CREATE_REQUEST', 'request', $req->request_id, $req->request_code);

            DB::commit();
            return redirect()->route('requests.show', $req->request_id)
                ->with('success', "Request {$req->request_code} submitted successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: '.$e->getMessage())->withInput();
        }
    }

    public function show($id) {
        $req = MaintenanceRequest::with([
            'teacher','department','equipment',
            'assignments.technician','assignments.assigner',
            'diagnoses.technician','diagnoses.verifier',
            'materialRequests.item','materialRequests.technician'
        ])->findOrFail($id);
        return view('requests.show', compact('req'));
    }

    public function assign(Request $request, $id) {
        $request->validate(['technician_id' => 'required|exists:users,user_id']);
        $req = MaintenanceRequest::findOrFail($id);
        $tech = User::findOrFail($request->technician_id);

        TaskAssignment::create([
            'request_id'    => $req->request_id,
            'technician_id' => $tech->user_id,
            'assigned_by'   => $request->user()->user_id,
            'status'        => 'pending',
        ]);

        $req->update(['status' => 'assigned']);

        notify($tech->user_id, "New Task Assigned", "You were assigned to {$req->request_code}.", 'info', route('tasks.index'));
        notify($req->teacher_id, "Technician Assigned", "{$tech->full_name} was assigned to {$req->request_code}.", 'success', route('requests.show', $req->request_id));

        audit('ASSIGN_TASK', 'request', $req->request_id, "To: {$tech->full_name}");

        return back()->with('success', "Assigned to {$tech->full_name}.");
    }

    public function updateStatus(Request $request, $id) {
        $request->validate(['status' => 'required|in:pending,review,assigned,in_progress,for_verification,completed,cancelled']);
        $req = MaintenanceRequest::findOrFail($id);
        $status = $request->status;

        $req->update([
            'status'         => $status,
            'date_completed' => $status === 'completed' ? now() : $req->date_completed,
        ]);

        // Sync assignment status
        if (in_array($status, ['in_progress','for_verification','completed'])) {
            $map = ['in_progress'=>'in_progress','for_verification'=>'for_review','completed'=>'completed'];
            TaskAssignment::where('request_id', $req->request_id)
                ->whereIn('status', ['pending','in_progress','for_review'])
                ->update(['status' => $map[$status]]);
        }

        notify($req->teacher_id, "Request Updated", "{$req->request_code} is now: ".ucwords(str_replace('_',' ', $status)), 'info', route('requests.show', $req->request_id));
        audit('UPDATE_STATUS', 'request', $req->request_id, $status);

        return back()->with('success', 'Status updated.');
    }

    public function verifyDiagnosis(Request $request, $diagnosisId) {
        $d = Diagnosis::findOrFail($diagnosisId);
        $d->update([
            'is_verified'  => true,
            'verified_by'  => $request->user()->user_id,
            'verified_at'  => now(),
        ]);
        notify($d->technician_id, "Diagnosis Verified", "Your diagnosis was verified.", 'success', route('requests.show', $d->request_id));
        audit('VERIFY_DIAGNOSIS', 'diagnosis', $diagnosisId);
        return back()->with('success', 'Diagnosis verified.');
    }
}