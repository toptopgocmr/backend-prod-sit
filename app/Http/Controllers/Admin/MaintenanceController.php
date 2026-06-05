<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{MaintenanceLog, Equipment};
use Illuminate\Http\Request;

class MaintenanceController extends Controller {
    public function index() {
        $logs      = MaintenanceLog::with(['equipment','reporter'])->latest()->paginate(20);
        $equipment = Equipment::all();
        return view('maintenance.index', compact('logs','equipment'));
    }
    public function create() { return view('maintenance.create', ['equipment'=>Equipment::all()]); }
    public function store(Request $request) {
        $validated = $request->validate(['equipment_id'=>'required|exists:equipment,id','type'=>'required|in:preventive,corrective,urgence','title'=>'required|string|max:200','description'=>'required|string','scheduled_date'=>'nullable|date']);
        $validated['reported_by'] = auth()->id();
        $validated['status']      = 'signale';
        MaintenanceLog::create($validated);
        if (in_array($validated['type'],['corrective','urgence'])) {
            Equipment::find($validated['equipment_id'])->update(['status'=>'en_panne']);
        }
        return redirect()->route('maintenance.index')->with('success','Intervention signalée.');
    }
    public function show(MaintenanceLog $maintenance) { $maintenance->load(['equipment','reporter']); return view('maintenance.show', compact('maintenance')); }
    public function edit(MaintenanceLog $maintenance) { return view('maintenance.edit', compact('maintenance')); }
    public function update(Request $request, MaintenanceLog $maintenance) { return back(); }
    public function destroy(MaintenanceLog $maintenance) { $maintenance->delete(); return redirect()->route('maintenance.index'); }
    public function resolve(Request $request, MaintenanceLog $maintenance) {
        $maintenance->update(['status'=>'resolu','resolved_at'=>now(),'resolution'=>$request->resolution,'cost'=>$request->cost ?? 0]);
        $maintenance->equipment->update(['status'=>'operationnel','last_maintenance_date'=>now()->toDateString(),'next_maintenance_date'=>now()->addDays($maintenance->equipment->maintenance_interval_days)->toDateString()]);
        return back()->with('success','Intervention résolue.');
    }
    public function assign(Request $request, MaintenanceLog $maintenance) {
        $maintenance->update(['assigned_to'=>$request->assigned_to,'started_at'=>now(),'status'=>'en_cours']);
        return back()->with('success','Technicien assigné.');
    }
}
