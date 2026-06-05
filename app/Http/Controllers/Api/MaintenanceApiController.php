<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Equipment, MaintenanceLog};
use Illuminate\Http\Request;

class MaintenanceApiController extends Controller {
    public function equipment() { return response()->json(Equipment::all()); }
    public function index()     { return response()->json(MaintenanceLog::with(['equipment','reporter'])->latest()->paginate(20)); }
    public function store(Request $request) {
        $log = MaintenanceLog::create(array_merge($request->validate(['equipment_id'=>'required|exists:equipment,id','type'=>'required|in:preventive,corrective,urgence','title'=>'required|string','description'=>'required|string']),['reported_by'=>auth()->id(),'status'=>'signale']));
        if (in_array($request->type,['corrective','urgence'])) Equipment::find($request->equipment_id)->update(['status'=>'en_panne']);
        return response()->json($log, 201);
    }
    public function resolve(Request $request, MaintenanceLog $log) {
        $log->update(['status'=>'resolu','resolved_at'=>now(),'resolution'=>$request->resolution,'cost'=>$request->cost??0]);
        $log->equipment->update(['status'=>'operationnel','last_maintenance_date'=>now()->toDateString()]);
        return response()->json($log);
    }
}
