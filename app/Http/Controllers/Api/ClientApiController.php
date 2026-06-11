<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientApiController extends Controller {
    public function index(Request $request) {
        $clients = Client::when($request->search, fn($q,$s) =>
            $q->where('first_name','like',"%$s%")
              ->orWhere('last_name','like',"%$s%")
              ->orWhere('phone','like',"%$s%")
        )->paginate(20);

        // Add computed fields for mobile
        $clients->getCollection()->transform(function ($c) {
            $c->name         = trim($c->first_name . ' ' . $c->last_name);
            $c->total_orders = $c->orders()->count() + $c->customOrders()->count();
            $c->total_spent  = $c->orders()->sum('amount_paid') + $c->customOrders()->sum('amount_paid');
            return $c;
        });

        return response()->json($clients);
    }
    public function show(Client $client) { return response()->json($client->load(['measurements'])); }
    public function store(Request $request) {
        $client = Client::create($request->validate(['first_name'=>'required|string','last_name'=>'required|string','phone'=>'required|string','gender'=>'required|in:homme,femme,non_precise']));
        return response()->json($client, 201);
    }
    public function update(Request $request, Client $client) { $client->update($request->all()); return response()->json($client); }
    public function destroy(Client $client) { $client->delete(); return response()->json(['message'=>'Supprimé.']); }
    public function measurements(Client $client) { return response()->json($client->measurements); }
    public function storeMeasurement(Request $request, Client $client) {
        $validated = $request->validate([
            'label'             => 'required|string|max:100',
            // Legacy fields
            'poitrine'          => 'nullable|numeric',
            'taille'            => 'nullable|numeric',
            'hanches'           => 'nullable|numeric',
            'epaules'           => 'nullable|numeric',
            'cou'               => 'nullable|numeric',
            'bras'              => 'nullable|numeric',
            'longueur_manche'   => 'nullable|numeric',
            'longueur_robe'     => 'nullable|numeric',
            'longueur_pantalon' => 'nullable|numeric',
            'entrejambe'        => 'nullable|numeric',
            'notes'             => 'nullable|string',
            'is_default'        => 'boolean',
        ]);

        // Collecter les champs genre-spécifiques (f_*, h_*, e_*)
        $legacyKeys = ['label','poitrine','taille','hanches','epaules','cou','bras',
                       'longueur_manche','longueur_robe','longueur_pantalon','entrejambe',
                       'notes','is_default'];
        $jsonFields = collect($request->all())
            ->filter(fn($v, $k) => !in_array($k, $legacyKeys) && ($v !== null && $v !== ''))
            ->toArray();

        if ($validated['is_default'] ?? false) {
            $client->measurements()->update(['is_default' => false]);
        }

        $m = $client->measurements()->create(array_merge(
            $validated,
            ['values' => !empty($jsonFields) ? $jsonFields : null]
        ));

        return response()->json($m, 201);
    }
}
