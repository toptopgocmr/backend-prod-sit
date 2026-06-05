<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;

class DeliveryApiController extends Controller {
    public function index(Request $request) {
        return response()->json(Delivery::with(['client'])->when(auth()->user()->isDelivery(),fn($q)=>$q->where('driver_id',auth()->id()))->latest()->paginate(20));
    }
    public function updateStatus(Request $request, Delivery $delivery) {
        $request->validate(['status'=>'required|in:pending,assigned,in_transit,delivered,failed,returned']);
        $delivery->update(['status'=>$request->status]);
        return response()->json($delivery);
    }
    public function uploadProof(Request $request, Delivery $delivery) {
        $path = $request->file('proof_photo')->store('deliveries/proofs','public');
        $delivery->update(['proof_photo'=>$path,'status'=>'delivered','delivered_at'=>now()]);
        return response()->json($delivery);
    }
    public function updateLocation(Request $request, Delivery $delivery) {
        $request->validate(['latitude'=>'required|numeric','longitude'=>'required|numeric']);
        $delivery->update(['latitude'=>$request->latitude,'longitude'=>$request->longitude]);
        return response()->json(['message'=>'Position mise à jour.']);
    }
}
