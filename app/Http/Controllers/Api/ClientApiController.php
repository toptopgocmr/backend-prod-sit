<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientApiController extends Controller {
    public function index(Request $request) {
        return response()->json(Client::when($request->search, fn($q,$s) => $q->where('first_name','like',"%$s%")->orWhere('phone','like',"%$s%"))->paginate(20));
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
        $m = $client->measurements()->create($request->validate(['label'=>'required|string','poitrine'=>'nullable|numeric','taille'=>'nullable|numeric','hanches'=>'nullable|numeric']));
        return response()->json($m, 201);
    }
}
