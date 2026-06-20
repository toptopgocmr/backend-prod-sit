<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Delivery, User, Client};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::with(['client', 'driver'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $deliveries = $query->paginate(20)->withQueryString();
        $drivers    = User::where('role', 'delivery')->where('is_active', true)->get();

        return view('livraison.index', compact('deliveries', 'drivers'));
    }

    public function create()
    {
        return view('livraison.create', [
            'clients' => Client::orderBy('first_name')->get(),
            'drivers' => User::where('role', 'delivery')->where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'        => 'required|exists:clients,id',
            'type'             => 'required|in:livraison,retrait_boutique',
            'delivery_address' => 'nullable|string|max:500',
            'delivery_fee'     => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string|max:1000',
            'driver_id'        => 'nullable|exists:users,id',
        ]);

        $validated['reference'] = 'LIV-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        $validated['status']    = $validated['driver_id'] ?? false ? 'assigned' : 'pending';

        if (!empty($validated['driver_id'])) {
            $validated['assigned_at'] = now();
        }

        Delivery::create($validated);

        return redirect()->route('deliveries.index')->with('success', 'Livraison créée avec succès.');
    }

    public function show(Delivery $delivery)
    {
        $delivery->load(['client', 'driver']);

        return view('livraison.show', compact('delivery'));
    }

    public function edit(Delivery $delivery)
    {
        return view('livraison.edit', compact('delivery'));
    }

    public function update(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'type'             => 'required|in:livraison,retrait_boutique',
            'delivery_address' => 'nullable|string|max:500',
            'delivery_fee'     => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $delivery->update($validated);

        return redirect()
            ->route('deliveries.show', $delivery)
            ->with('success', 'Livraison mise à jour avec succès.');
    }

    public function destroy(Delivery $delivery)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Accès réservé à l\'administrateur.');
        $delivery->delete();
        return redirect()->route('deliveries.index')->with('success', 'Livraison supprimée.');
    }

    public function assignDriver(Request $request, Delivery $delivery)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
        ]);

        $delivery->update([
            'driver_id'   => $request->driver_id,
            'assigned_at' => now(),
            'status'      => 'assigned',
        ]);

        return back()->with('success', 'Livreur assigné avec succès.');
    }

    public function updateStatus(Request $request, Delivery $delivery)
    {
        $request->validate([
            'status' => 'required|in:pending,assigned,in_transit,delivered,failed,returned',
        ]);

        $delivery->update(['status' => $request->status]);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function uploadProof(Request $request, Delivery $delivery)
    {
        $request->validate([
            'proof_photo' => 'required|image|max:5120',
        ]);

        $path = $request->file('proof_photo')->store('deliveries/proofs', 'public');

        $delivery->update([
            'proof_photo'  => $path,
            'status'       => 'delivered',
            'delivered_at' => now(),
        ]);

        return back()->with('success', 'Preuve de livraison enregistrée.');
    }
}
