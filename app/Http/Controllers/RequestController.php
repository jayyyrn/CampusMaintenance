<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{MaintenanceRequest, Equipment, TaskAssignment, User, Diagnosis};
use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\ResponseMimeType;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;

class RequestController extends Controller
{
    public function scan(Request $request)
{
    $request->validate([
        'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:8192',
    ]);

    try {
        $image = $request->file('image');

        $blob = new Blob(
            mimeType: MimeType::from($image->getMimeType()),
            data: base64_encode(file_get_contents($image->getPathname()))
        );

        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'unit_no'             => new Schema(type: DataType::STRING),
                'description'         => new Schema(type: DataType::STRING),
                'date_start'          => new Schema(type: DataType::STRING),
                'date_finish'         => new Schema(type: DataType::STRING),
                'manpower'            => new Schema(type: DataType::STRING),
                'tools_and_materials' => new Schema(type: DataType::STRING),
                'estimated_budget'    => new Schema(type: DataType::STRING),
                'category'            => new Schema(type: DataType::STRING),
                'priority'            => new Schema(type: DataType::STRING),
            ],
            required: ['unit_no','description','category','priority']
        );

        $config = new GenerationConfig(
            responseMimeType: ResponseMimeType::APPLICATION_JSON,
            responseSchema: $schema
        );

        $prompt = <<<PROMPT
Read this Philippine university Job Order / Work Request form.
Extract every field. Return "" for blank fields.
Transcribe handwriting as best you can.
For "category", pick ONE of: electrical, carpentry, fabrication, aircon, plumbing, general.
For "priority", pick ONE of: low, medium, high, urgent (default "medium" if unsure).
PROMPT;

        $result = Gemini::generativeModel('gemini-1.5-flash')
            ->withGenerationConfig($config)
            ->generateContent([$prompt, $blob]);

        return response()->json([
            'ok'     => true,
            'fields' => $result->json(),
        ]);

    } catch (\Throwable $e) {
        \Log::error('Gemini scan failed: ' . $e->getMessage());
        return response()->json([
            'ok'    => false,
            'error' => 'Could not read the form. Try a clearer photo or fill it manually.',
        ], 422);
    }
}
    public function index(Request $request)
    {
        $user     = $request->user();
        $q        = $request->get('q');
        $status   = $request->get('status');
        $category = $request->get('category');
        $priority = $request->get('priority');

        $query = MaintenanceRequest::with(['teacher','department','activeAssignment.technician']);

        // Teacher: only their own
        if ($user->isTeacher()) {
            $query->where('teacher_id', $user->user_id);
        }
        // Regular technician only: only requests assigned to them.
        // Lead technicians, coordinators, admins see everything.
        elseif ($user->role === 'technician') {
            $query->whereHas('assignments', fn($q) => $q->where('technician_id', $user->user_id));
        }

        if ($q) {
            $query->where(function ($x) use ($q) {
                $x->where('request_code', 'like', "%$q%")
                  ->orWhere('title', 'like', "%$q%")
                  ->orWhere('location', 'like', "%$q%")
                  ->orWhere('description', 'like', "%$q%");
            });
        }
        if ($status && in_array($status, ['pending','review','assigned','in_progress','for_verification','completed','cancelled'])) {
            $query->where('status', $status);
        }
        if ($category && in_array($category, ['electrical','carpentry','fabrication','aircon','plumbing','general'])) {
            $query->where('category', $category);
        }
        if ($priority && in_array($priority, ['low','medium','high','urgent'])) {
            $query->where('priority', $priority);
        }

        $requests = $query->byPriority()
            ->orderByDesc('date_reported')
            ->paginate(15)
            ->withQueryString();

        return view('requests.index', compact('requests', 'q', 'status', 'category', 'priority'));
    }

    public function create()
    {
        $equipment = Equipment::orderBy('equipment_name')->get();
        return view('requests.create', compact('equipment'));
    }

    public function store(Request $request)
{
    $data = $request->validate([
    'title'               => 'required|string|max:200',
    'description'         => 'required|string|min:10',
    'category'            => 'required|in:electrical,carpentry,fabrication,aircon,plumbing,general,other',
    'custom_category'     => 'nullable|string|max:100|required_if:category,other',
    'location'            => 'nullable|string|max:100',
    'photo_before'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
    'unit_no'             => 'nullable|string|max:50',
    'tools_and_materials' => 'nullable|string',
    'estimated_budget'    => 'nullable|numeric|min:0',
    'date_start'          => 'nullable|string|max:50',
    'date_finish'         => 'nullable|string|max:50',
], [
    'description.min'        => 'Please describe the problem in at least 10 characters.',
    'custom_category.required_if' => 'Please type your custom category.',
]);

    DB::beginTransaction();
    try {
        $photoPath = null;
        if ($request->hasFile('photo_before')) {
            $photoPath = $request->file('photo_before')->store('requests', 'public');
        }

        $req = MaintenanceRequest::create([
    'request_code'        => generate_request_code(),
    'teacher_id'          => $request->user()->user_id,
    'department_id'       => $request->user()->department_id,
    'category'            => $data['category'] === 'other' ? 'general' : $data['category'],
    'custom_category'     => $data['category'] === 'other' ? $data['custom_category'] : null,
    'title'               => $data['title'],
    'description'         => $data['description'],
    'location'            => $data['location'] ?? null,
    'priority'            => 'medium',   // ← forced default, teachers don't set it
    'photo_before'        => $photoPath,
    'unit_no'             => $data['unit_no'] ?? null,
    'tools_and_materials' => $data['tools_and_materials'] ?? null,
    'estimated_budget'    => $data['estimated_budget'] ?? null,
    'date_start'          => $data['date_start'] ?? null,
    'date_finish'         => $data['date_finish'] ?? null,
]);

        update_queue_positions();

        foreach (User::whereIn('role', ['coordinator','lead_technician','admin'])
                    ->where('status', 'active')->get() as $a) {
            notify($a->user_id,
                "New Request: {$req->request_code}",
                $req->title, 'info',
                route('requests.show', $req->request_id));
        }

        audit('CREATE_REQUEST', 'request', $req->request_id, $req->request_code);

        DB::commit();
        return redirect()->route('requests.show', $req->request_id)
            ->with('success', "Request {$req->request_code} submitted successfully.");
    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error('Request create failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return back()->with('error', 'Failed to submit request. Please try again.')->withInput();
    }
}

    public function show(Request $request, $id)
    {
        $req = MaintenanceRequest::with([
            'teacher', 'department', 'equipment',
            'assignments.technician', 'assignments.assigner',
            'diagnoses.technician', 'diagnoses.verifier',
            'materialRequests.item', 'materialRequests.technician',
        ])->findOrFail($id);

        $user = $request->user();

        // Teacher: only their own
        if ($user->isTeacher() && $req->teacher_id !== $user->user_id) {
            abort(403, 'You can only view your own requests.');
        }
        // Regular technician only: only if assigned.
        // Lead technicians can view any request.
        if ($user->role === 'technician') {
            $isAssigned = $req->assignments->contains('technician_id', $user->user_id);
            if (!$isAssigned) {
                abort(403, 'You are not assigned to this request.');
            }
        }

        return view('requests.show', compact('req'));
    }

    public function assign(Request $request, $id)
    {
        $request->validate(['technician_id' => 'required|exists:users,user_id']);

        $req  = MaintenanceRequest::findOrFail($id);
        $tech = User::findOrFail($request->technician_id);

        if (!in_array($tech->role, ['technician','lead_technician'])) {
            return back()->with('error', 'Selected user is not a technician.');
        }
        if ($tech->status !== 'active') {
            return back()->with('error', 'Selected technician is inactive.');
        }
        if (in_array($req->status, ['completed','cancelled'])) {
            return back()->with('error', 'Cannot assign to a closed request.');
        }

        DB::beginTransaction();
        try {
            $exists = TaskAssignment::where('request_id', $req->request_id)
                ->where('technician_id', $tech->user_id)
                ->whereIn('status', ['pending','in_progress','for_review'])
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                DB::rollBack();
                return back()->with('error', "{$tech->full_name} is already assigned to this request.");
            }

            TaskAssignment::create([
                'request_id'    => $req->request_id,
                'technician_id' => $tech->user_id,
                'assigned_by'   => $request->user()->user_id,
                'status'        => 'pending',
            ]);

            if (in_array($req->status, ['pending','review'])) {
                $req->update(['status' => 'assigned']);
            }

            notify($tech->user_id, "New Task Assigned",
                "You were assigned to {$req->request_code}.",
                'info', route('tasks.index'));

            notify($req->teacher_id, "Technician Assigned",
                "{$tech->full_name} was assigned to {$req->request_code}.",
                'success', route('requests.show', $req->request_id));

            audit('ASSIGN_TASK', 'request', $req->request_id, "To: {$tech->full_name}");

            DB::commit();
            return back()->with('success', "Assigned to {$tech->full_name}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Assign failed: ' . $e->getMessage());
            return back()->with('error', 'Assignment failed. Please try again.');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,review,assigned,in_progress,for_verification,completed,cancelled',
        ]);

        $req    = MaintenanceRequest::findOrFail($id);
        $user   = $request->user();
        $status = $request->status;

        // Regular technician only: only assigned requests
        if ($user->role === 'technician') {
            $isAssigned = $req->assignments()
                ->where('technician_id', $user->user_id)
                ->whereIn('status', ['pending','in_progress','for_review'])
                ->exists();
            if (!$isAssigned) {
                abort(403, 'You are not assigned to this request.');
            }
        }

        DB::beginTransaction();
        try {
            $req->update([
                'status'         => $status,
                'date_completed' => $status === 'completed' ? now() : $req->date_completed,
            ]);

            if (in_array($status, ['in_progress','for_verification','completed'])) {
                $map = [
                    'in_progress'      => 'in_progress',
                    'for_verification' => 'for_review',
                    'completed'        => 'completed',
                ];
                TaskAssignment::where('request_id', $req->request_id)
                    ->whereIn('status', ['pending','in_progress','for_review'])
                    ->update(['status' => $map[$status]]);
            }

            if (in_array($status, ['completed','cancelled'])) {
                update_queue_positions();
            }

            notify($req->teacher_id, "Request Updated",
                "{$req->request_code} is now: " . ucwords(str_replace('_', ' ', $status)),
                'info', route('requests.show', $req->request_id));

            audit('UPDATE_STATUS', 'request', $req->request_id, $status);

            DB::commit();
            return back()->with('success', 'Status updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Status update failed: ' . $e->getMessage());
            return back()->with('error', 'Update failed. Please try again.');
        }
    }

    public function verifyDiagnosis(Request $request, $diagnosisId)
    {
        $d = Diagnosis::findOrFail($diagnosisId);

        if ($d->is_verified) {
            return back()->with('error', 'This diagnosis is already verified.');
        }

        $d->update([
            'is_verified' => true,
            'verified_by' => $request->user()->user_id,
            'verified_at' => now(),
        ]);

        notify($d->technician_id, "Diagnosis Verified",
            "Your diagnosis was verified.",
            'success', route('requests.show', $d->request_id));

        audit('VERIFY_DIAGNOSIS', 'diagnosis', $diagnosisId);

        return back()->with('success', 'Diagnosis verified.');
    }
}