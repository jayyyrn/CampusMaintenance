<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Inventory, MaterialRequest, StockTransaction, User};

class InventoryController extends Controller
{
    public function index(Request $request) {
        $q = $request->get('q');
        $query = Inventory::query();
        if ($q) $query->where('item_name','like',"%$q%")->orWhere('item_code','like',"%$q%");
        $items = $query->orderBy('item_name')->paginate(20);

        $lowStock = Inventory::whereColumn('qty_on_hand','<=','min_stock_level')->get();
        return view('inventory.index', compact('items','lowStock','q'));
    }

    public function materialRequests(Request $request) {
        $user = $request->user();

        if ($user->isTechnician()) {
            $requests = MaterialRequest::with(['item','request'])
                ->where('technician_id', $user->user_id)
                ->latest('requested_at')->get();
        } else {
            $requests = MaterialRequest::with(['item','request','technician'])
                ->orderByRaw("FIELD(status,'pending','approved','released','returned','rejected')")
                ->latest('requested_at')->get();
        }
        return view('inventory.material_requests', compact('requests'));
    }

    public function requestMaterial(Request $request) {
        $data = $request->validate([
            'item_id'         => 'required|exists:inventory,item_id',
            'quantity'        => 'required|integer|min:1',
            'request_id'      => 'nullable|exists:maintenance_requests,request_id',
            'notes'           => 'nullable|string',
        ]);

        $mr = MaterialRequest::create([
            'request_id'         => $data['request_id'] ?? null,
            'technician_id'      => $request->user()->user_id,
            'item_id'            => $data['item_id'],
            'quantity_requested' => $data['quantity'],
            'notes'              => $data['notes'] ?? null,
        ]);

        // Notify supervisor + inventory officer
        foreach (User::whereIn('role',['lead_technician','inventory_officer','admin'])->where('status','active')->get() as $u) {
            notify($u->user_id, "Material Request", "A technician requested materials.", 'info', route('inventory.material_requests'));
        }

        audit('REQUEST_MATERIAL', 'material_request', $mr->mat_req_id);
        return back()->with('success', 'Material requested. Awaiting approval.');
    }

    public function approveMaterial(Request $request, $id) {
        $mr = MaterialRequest::findOrFail($id);
        $mr->update([
            'status'      => 'approved',
            'approved_by' => $request->user()->user_id,
            'approved_at' => now(),
        ]);
        notify($mr->technician_id, "Material Approved", "Your material request was approved.", 'success', route('inventory.material_requests'));
        audit('APPROVE_MATERIAL', 'material_request', $id);
        return back()->with('success', 'Approved.');
    }

    public function releaseMaterial(Request $request, $id) {
        $mr = MaterialRequest::findOrFail($id);
        $item = $mr->item;

        if ($item->qty_on_hand < $mr->quantity_requested) {
            return back()->with('error', "Insufficient stock. On hand: {$item->qty_on_hand}");
        }

        DB::beginTransaction();
        try {
            $mr->update([
                'status'            => 'released',
                'released_by'       => $request->user()->user_id,
                'released_at'       => now(),
                'quantity_released' => $mr->quantity_requested,
            ]);

            $item->decrement('qty_on_hand', $mr->quantity_requested);

            StockTransaction::create([
                'item_id'          => $item->item_id,
                'transaction_type' => 'stock_out',
                'quantity'         => $mr->quantity_requested,
                'request_id'       => $mr->request_id,
                'performed_by'     => $request->user()->user_id,
                'notes'            => "Released for material request #{$mr->mat_req_id}",
            ]);

            notify($mr->technician_id, "Material Released", "Your material request has been released.", 'success', route('inventory.material_requests'));
            audit('RELEASE_MATERIAL', 'material_request', $id);
            DB::commit();
            return back()->with('success', 'Material released.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: '.$e->getMessage());
        }
    }

    public function returnMaterial(Request $request, $id) {
        $data = $request->validate(['quantity' => 'required|integer|min:1']);
        $mr = MaterialRequest::findOrFail($id);

        DB::beginTransaction();
        try {
            $mr->increment('quantity_returned', $data['quantity']);
            $mr->update(['status' => 'returned']);
            $mr->item->increment('qty_on_hand', $data['quantity']);

            StockTransaction::create([
                'item_id'          => $mr->item_id,
                'transaction_type' => 'return',
                'quantity'         => $data['quantity'],
                'request_id'       => $mr->request_id,
                'performed_by'     => $request->user()->user_id,
                'notes'            => "Returned for material request #{$mr->mat_req_id}",
            ]);

            audit('RETURN_MATERIAL', 'material_request', $id);
            DB::commit();
            return back()->with('success', 'Material returned.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: '.$e->getMessage());
        }
    }

    public function stockIn(Request $request) {
        $data = $request->validate([
            'item_id'      => 'required|exists:inventory,item_id',
            'quantity'     => 'required|integer|min:1',
            'reference_no' => 'nullable|string',
            'supplier'     => 'nullable|string',
            'notes'        => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $item = Inventory::findOrFail($data['item_id']);
            $item->increment('qty_on_hand', $data['quantity']);

            StockTransaction::create([
                'item_id'          => $item->item_id,
                'transaction_type' => 'stock_in',
                'quantity'         => $data['quantity'],
                'reference_no'     => $data['reference_no'] ?? null,
                'performed_by'     => $request->user()->user_id,
                'supplier'         => $data['supplier'] ?? null,
                'notes'            => $data['notes'] ?? null,
            ]);

            audit('STOCK_IN', 'inventory', $item->item_id, "Qty: {$data['quantity']}");
            DB::commit();
            return back()->with('success', 'Stock added.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: '.$e->getMessage());
        }
    }

    public function transactions() {
        $txns = StockTransaction::with(['item','performer'])
            ->latest()->paginate(30);
        return view('inventory.transactions', compact('txns'));
    }
}