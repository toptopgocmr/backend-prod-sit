<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Equipment, MaintenanceLog};
use Illuminate\Http\Request;

class MaintenanceApiController extends Controller {

    // ── Équipements ────────────────────────────────────────────────────
    public function equipment() {
        return response()->json(['data' => Equipment::latest()->get()]);
    }

    public function storeEquipment(Request $request) {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'brand'         => 'nullable|string',
            'model'         => 'nullable|string',
            'serial_number' => 'nullable|string',
            'location'      => 'nullable|string',
            'type'          => 'nullable|string',
            'notes'         => 'nullable|string',
        ]);
        $data['status'] = 'operationnel';
        return response()->json(Equipment::create($data), 201);
    }

    public function updateEquipment(Request $request, $id) {
        $equipment = Equipment::findOrFail($id);
        $data = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'brand'         => 'nullable|string',
            'model'         => 'nullable|string',
            'serial_number' => 'nullable|string',
            'location'      => 'nullable|string',
            'type'          => 'nullable|string',
            'is_active'     => 'nullable|boolean',
            'status'        => 'nullable|in:operationnel,en_panne,en_maintenance,hors_service',
            'notes'         => 'nullable|string',
        ]);
        $equipment->update($data);
        return response()->json($equipment->fresh());
    }

    public function destroyEquipment($id) {
        $equipment = Equipment::findOrFail($id);
        // Prevent deletion if has active maintenance logs
        if ($equipment->maintenanceLogs()->where('status','!=','resolu')->exists()) {
            return response()->json(['message' => 'Cet équipement a des interventions en cours.'], 422);
        }
        $equipment->delete();
        return response()->json(['message' => 'Équipement supprimé.']);
    }

    // ── Interventions ──────────────────────────────────────────────────
    public function index() {
        return response()->json(MaintenanceLog::with(['equipment','reporter'])->latest()->paginate(20));
    }

    public function store(Request $request) {
        $log = MaintenanceLog::create(array_merge(
            $request->validate([
                'equipment_id' => 'required|exists:equipment,id',
                'type'         => 'required|in:preventive,corrective,urgence',
                'title'        => 'required|string',
                'description'  => 'required|string',
            ]),
            ['reported_by' => auth()->id(), 'status' => 'signale']
        ));
        if (in_array($request->type, ['corrective', 'urgence'])) {
            Equipment::find($request->equipment_id)->update(['status' => 'en_panne']);
        }
        return response()->json($log, 201);
    }

    public function resolve(Request $request, MaintenanceLog $log) {
        $log->update([
            'status'      => 'resolu',
            'resolved_at' => now(),
            'resolution'  => $request->resolution,
            'cost'        => $request->cost ?? 0,
        ]);
        $log->equipment->update([
            'status'                 => 'operationnel',
            'last_maintenance_date'  => now()->toDateString(),
        ]);
        return response()->json($log);
    }
}
