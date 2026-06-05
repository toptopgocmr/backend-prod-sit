<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderApiController extends Controller {
    public function index(Request $request) {
        return response()->json(Order::with(['client'])->when($request->status,fn($q,$s)=>$q->where('status',$s))->latest()->paginate(15));
    }
    public function show(Order $order)   { return response()->json($order->load(['client','items.product'])); }
    public function store(Request $r)    { return response()->json(['message'=>'Use web form'], 501); }
    public function update(Request $r, Order $o) { return response()->json($o); }
    public function destroy(Order $o)    { $o->delete(); return response()->json(['message'=>'Supprimé']); }
    public function updateStatus(Request $request, Order $order) {
        $request->validate(['status'=>'required|in:pending,confirmed,processing,ready,delivered,cancelled']);
        $order->update(['status'=>$request->status]);
        return response()->json($order);
    }
    public function payment(Request $request, Order $order) {
        $request->validate(['amount'=>'required|numeric|min:1','method'=>'required|string']);
        $order->increment('amount_paid', $request->amount);
        $order->update(['payment_status'=>$order->amount_paid>=$order->total?'paid':'partial']);
        return response()->json($order);
    }
}
