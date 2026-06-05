<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller {
    public function index() {
        $equipment = Equipment::withCount('maintenanceLogs')->get();
        $stats = ['total'=>$equipment->count(),'operationnel'=>$equipment->where('status','operationnel')->count(),'en_panne'=>$equipment->where('status','en_panne')->count(),'overdue'=>$equipment->filter(fn($e)=>$e->isOverdue())->count()];
        return view('maintenance.equipment', compact('equipment','stats'));
    }
    public function create() { return view('maintenance.equipment-create'); }
    public function store(Request $request) {
        Equipment::create($request->validate(['name'=>'required|string|max:200','type'=>'required|in:machine_a_coudre,climatiseur,groupe_electrogene,ordinateur,autre','brand'=>'nullable|string','location'=>'nullable|string','maintenance_interval_days'=>'required|integer|min:1','notes'=>'nullable|string']));
        return redirect()->route('equipment.index')->with('success','Équipement ajouté.');
    }
    public function show(Equipment $equipment) { $equipment->load('maintenanceLogs.reporter'); return view('maintenance.equipment-show', compact('equipment')); }
    public function edit(Equipment $equipment) { return view('maintenance.equipment-edit', compact('equipment')); }
    public function update(Request $request, Equipment $equipment) { $equipment->update($request->all()); return back()->with('success','Mis à jour.'); }
    public function destroy(Equipment $equipment) { $equipment->delete(); return redirect()->route('equipment.index'); }
}
