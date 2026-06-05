<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{CustomOrder, CustomOrderStatus};
use Illuminate\Http\Request;

class CustomOrderApiController extends Controller {
    public function index(Request $request) {
        return response()->json(CustomOrder::with(['client','couturier'])->when($request->status,fn($q,$s)=>$q->where('status',$s))->latest()->paginate(15));
    }
    public function show(CustomOrder $customOrder) { return response()->json($customOrder->load(['client','measurement','couturier'])); }
    public function store(Request $r)  { return response()->json(['message'=>'Use web form'], 501); }
    public function update(Request $r, CustomOrder $co) { return response()->json($co); }
    public function destroy(CustomOrder $co) { $co->delete(); return response()->json(['message'=>'Supprimé']); }
    public function updateStatus(Request $request, CustomOrder $customOrder) {
        $request->validate(['status'=>'required|in:'.implode(',',array_keys(CustomOrder::STATUSES))]);
        $customOrder->update(['status'=>$request->status]);
        CustomOrderStatus::create(['custom_order_id'=>$customOrder->id,'user_id'=>auth()->id(),'status'=>$request->status,'comment'=>$request->comment]);
        return response()->json($customOrder);
    }
    public function assign(Request $request, CustomOrder $customOrder) {
        $request->validate(['assigned_to'=>'required|exists:users,id']);
        $customOrder->update(['assigned_to'=>$request->assigned_to]);
        return response()->json($customOrder);
    }
    public function payment(Request $request, CustomOrder $customOrder) {
        $request->validate(['amount'=>'required|numeric|min:1','method'=>'required|string']);
        $customOrder->increment('amount_paid', $request->amount);
        $customOrder->update(['payment_status'=>$customOrder->amount_paid>=$customOrder->total?'paid':'partial']);
        return response()->json($customOrder);
    }
}
