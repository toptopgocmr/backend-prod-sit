<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderApiController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('user')->latest();

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(30);

        return response()->json([
            'data' => $orders->map(fn($o) => $this->format($o)),
            'total' => $orders->total(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_name'   => 'required|string|max:255',
            'supplier_phone'  => 'nullable|string',
            'total_amount'    => 'required|numeric|min:0',
            'amount_paid'     => 'nullable|numeric|min:0',
            'status'          => 'nullable|in:pending,ordered,received,cancelled',
            'expected_date'   => 'nullable|date',
            'received_date'   => 'nullable|date',
            'payment_method'  => 'nullable|string',
            'notes'           => 'nullable|string',
        ]);

        $data['user_id'] = auth()->id();
        $data['status']  = $data['status'] ?? 'pending';
        $data['reference'] = 'BC-' . strtoupper(substr(uniqid(), -6));

        $order = PurchaseOrder::create($data);

        return response()->json($this->format($order->fresh()), 201);
    }

    public function update(Request $request, $id)
    {
        $order = PurchaseOrder::findOrFail($id);

        $data = $request->validate([
            'supplier_name'   => 'sometimes|string|max:255',
            'supplier_phone'  => 'nullable|string',
            'total_amount'    => 'sometimes|numeric|min:0',
            'amount_paid'     => 'nullable|numeric|min:0',
            'status'          => 'sometimes|in:pending,ordered,received,cancelled',
            'expected_date'   => 'nullable|date',
            'received_date'   => 'nullable|date',
            'payment_method'  => 'nullable|string',
            'notes'           => 'nullable|string',
        ]);

        $order->update($data);

        return response()->json($this->format($order->fresh()));
    }

    public function destroy($id)
    {
        $order = PurchaseOrder::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Bon de commande supprimé.']);
    }

    private function format(PurchaseOrder $o): array
    {
        return [
            'id'             => $o->id,
            'reference'      => $o->reference,
            'supplier_name'  => $o->supplier_name,
            'supplier_phone' => $o->supplier_phone,
            'total_amount'   => $o->total_amount,
            'amount_paid'    => $o->amount_paid ?? 0,
            'status'         => $o->status,
            'expected_date'  => $o->expected_date?->toDateString(),
            'received_date'  => $o->received_date?->toDateString(),
            'payment_method' => $o->payment_method,
            'notes'          => $o->notes,
            'created_by'     => $o->user?->name,
            'created_at'     => $o->created_at?->toISOString(),
        ];
    }
}
