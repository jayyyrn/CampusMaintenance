<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{MaintenanceRequest, Equipment, TaskAssignment, User, Diagnosis, Department};
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
                    'title'               => new Schema(type: DataType::STRING),
                    'description'         => new Schema(type: DataType::STRING),
                    'category'            => new Schema(type: DataType::STRING),
                    'location'            => new Schema(type: DataType::STRING),
                    'unit_no'             => new Schema(type: DataType::STRING),
                    'tools_and_materials' => new Schema(type: DataType::STRING),
                    'estimated_budget'    => new Schema(type: DataType::STRING),
                    'date_start'          => new Schema(type: DataType::STRING),
                    'date_finish'         => new Schema(type: DataType::STRING),
                ],
                required: ['title', 'description', 'category']
            );

            $config = new GenerationConfig(
                responseMimeType: ResponseMimeType::APPLICATION_JSON,
                responseSchema: $schema
            );

            $prompt = <<<PROMPT
You are reading an image that contains a maintenance request. The image could be:
1. A full Philippine university "Job Order / Work Request Form"
2. A simple list of handwritten or typed fields
3. A photo, screenshot, or scanned document

Extract the following fields and return them as JSON. Use "" for anything you can't find.

- title: Short summary. If "Description of request" or "Problem description" is present, use that.
- description: More detail.
- category: ONE of electrical, carpentry, fabrication, aircon, plumbing, general.
- location: Room number, unit number, or Department field.
- unit_no: "Unit No." value if present, otherwise "".
- tools_and_materials: Only if "TOOLS and MATERIALS" section exists, otherwise "".
- estimated_budget: Only if "ESTIMATED BUDGET" field exists, otherwise "".
- date_start: Only if "START" date exists, otherwise "".
- date_finish: Only if "FINISH" date exists, otherwise "".
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

        if ($user->isTeacher()) {
            $query->where('teacher_id', $user->user_id);
        } elseif ($user->role === 'technician') {
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
            'department_id'       => 'required',
            'custom_department'   => 'nullable|string|max:100|required_if:department_id,other',
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
            'description.min'               => 'Please describe the problem in at least 10 characters.',
            'custom_category.required_if'   => 'Please type your custom category.',
            'custom_department.required_if' => 'Please type the department name.',
            'department_id.required'        => 'Please select a department.',
        ]);

        DB::beginTransaction();
        try {
            // Resolve department
            if ($data['department_id'] === 'other') {
                $customName = trim($data['custom_department'] ?? '');
                if ($customName === '') {
                    DB::rollBack();
                    return back()->with('error', 'Please type the department name.')->withInput();
                }

                $existing = Department::whereRaw('LOWER(dept_name) = ?', [strtolower($customName)])->first();

                if ($existing) {
                    $resolvedDeptId = $existing->dept_id;
                } else {
                    $baseCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $customName), 0, 6));
                    if ($baseCode === '') $baseCode = 'DEPT';

                    $code = $baseCode;
                    $suffix = 1;
                    while (Department::where('dept_code', $code)->exists()) {
                        $code = $baseCode . $suffix;
                        $suffix++;
                    }

                    $newDept = Department::create([
                        'dept_name' => $customName,
                        'dept_code' => $code,
                    ]);
                    $resolvedDeptId = $newDept->dept_id;

                    audit('CREATE_DEPARTMENT', 'department', $newDept->dept_id, $newDept->dept_name);
                }
            } else {
                if (!Department::where('dept_id', $data['department_id'])->exists()) {
                    DB::rollBack();
                    return back()->with('error', 'Selected department is invalid.')->withInput();
                }
                $resolvedDeptId = (int) $data['department_id'];
            }

            $photoPath = null;
            if ($request->hasFile('photo_before')) {
                $photoPath = $request->file('photo_before')->store('requests', 'public');
            }

            $req = MaintenanceRequest::create([
                'request_code'        => generate_request_code(),
                'teacher_id'          => $request->user()->user_id,
                'department_id'       => $resolvedDeptId,
                'category'            => $data['category'] === 'other' ? 'general' : $data['category'],
                'custom_category'     => $data['category'] === 'other' ? $data['custom_category'] : null,
                'title'               => $data['title'],
                'description'         => $data['description'],
                'location'            => $data['location'] ?? null,
                'priority'            => 'medium',
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

        if ($user->isTeacher() && $req->teacher_id !== $user->user_id) {
            abort(403, 'You can only view your own requests.');
        }
        if ($user->role === 'technician') {
            $isAssigned = $req->assignments->contains('technician_id', $user->user_id);
            if (!$isAssigned) {
                abort(403, 'You are not assigned to this request.');
            }
        }

        return view('requests.show', compact('req'));
    }

    /* ═══════════════════════════════════════════════════════════════
       ASSIGN / REASSIGN
       Enforces ONE active primary technician per request.
       Previous active assignments are marked 'reassigned' and notified.
    ═══════════════════════════════════════════════════════════════ */
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
            // 1) Block if this exact technician is already active on this request
            $alreadyActive = TaskAssignment::where('request_id', $req->request_id)
                ->where('technician_id', $tech->user_id)
                ->whereIn('status', ['pending','in_progress','for_review'])
                ->lockForUpdate()
                ->exists();

            if ($alreadyActive) {
                DB::rollBack();
                return back()->with('error', "{$tech->full_name} is already the active technician for this request.");
            }

            // 2) Mark any OTHER active assignment(s) as reassigned
            $previousAssignments = TaskAssignment::where('request_id', $req->request_id)
                ->whereIn('status', ['pending','in_progress','for_review'])
                ->lockForUpdate()
                ->get();

            $previousTechIds = [];

            foreach ($previousAssignments as $prev) {
                $prev->update([
                    'status'   => 'reassigned',
                    'ended_at' => now(),
                ]);

                if ($prev->technician_id !== $tech->user_id) {
                    $previousTechIds[] = $prev->technician_id;
                }
            }

            // 3) Create the new primary assignment
            TaskAssignment::create([
                'request_id'    => $req->request_id,
                'technician_id' => $tech->user_id,
                'assigned_by'   => $request->user()->user_id,
                'status'        => 'pending',
            ]);

            // 4) Sync request status
            if (in_array($req->status, ['pending','review'])) {
                $req->update(['status' => 'assigned']);
            }

            // 5) Notify new technician
            notify($tech->user_id, "New Task Assigned",
                "You were assigned to {$req->request_code}.",
                'info', route('tasks.index'));

            // 6) Notify teacher
            notify($req->teacher_id, "Technician Assigned",
                "{$tech->full_name} was assigned to {$req->request_code}.",
                'success', route('requests.show', $req->request_id));

            // 7) Notify previous technicians (deduped)
            foreach (array_unique($previousTechIds) as $prevTechId) {
                notify($prevTechId, "Request Reassigned",
                    "{$req->request_code} has been reassigned to another technician.",
                    'warning', route('requests.show', $req->request_id));
            }

            // 8) Audit with reassignment context
            $reassignedCount = $previousAssignments->count();
            $details = $reassignedCount > 0
                ? "Reassigned from {$reassignedCount} previous tech(s) to: {$tech->full_name}"
                : "To: {$tech->full_name}";

            audit('ASSIGN_TASK', 'request', $req->request_id, $details);

            DB::commit();

            $message = $reassignedCount > 0
                ? "Reassigned to {$tech->full_name}. Previous technician(s) notified."
                : "Assigned to {$tech->full_name}.";

            return back()->with('success', $message);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Assign failed: ' . $e->getMessage());
            return back()->with('error', 'Assignment failed. Please try again.');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'   => 'required|in:pending,review,assigned,in_progress,for_verification,completed,cancelled',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $req    = MaintenanceRequest::findOrFail($id);
        $user   = $request->user();
        $status = $request->status;

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
            $update = [
                'status'         => $status,
                'date_completed' => $status === 'completed' ? now() : $req->date_completed,
            ];

            if ($request->filled('priority') && $user->isSupervisor()) {
                $update['priority'] = $request->priority;
            }

            $req->update($update);

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

            if (in_array($status, ['completed','cancelled']) || isset($update['priority'])) {
                update_queue_positions();
            }

            notify($req->teacher_id, "Request Updated",
                "{$req->request_code} is now: " . ucwords(str_replace('_', ' ', $status)),
                'info', route('requests.show', $req->request_id));

            audit('UPDATE_STATUS', 'request', $req->request_id, $status);

            DB::commit();
            return back()->with('success', 'Request updated.');
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