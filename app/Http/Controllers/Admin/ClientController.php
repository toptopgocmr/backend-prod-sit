<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Client, Measurement};
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::withCount(['orders','customOrders'])->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('first_name','like',"%$s%")
                  ->orWhere('last_name','like',"%$s%")
                  ->orWhere('phone','like',"%$s%")
                  ->orWhere('email','like',"%$s%");
            });
        }
        if ($request->filled('gender')) $query->where('gender', $request->gender);
        if ($request->filled('city'))   $query->where('city', $request->city);

        $clients = $query->paginate(20)->withQueryString();
        $cities  = Client::select('city')->whereNotNull('city')->distinct()->pluck('city');

        return view('clients.index', compact('clients','cities'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'required|string|max:100',
            'phone'       => 'required|string|max:30',
            'email'       => 'nullable|email|max:150',
            'address'     => 'nullable|string',
            'city'        => 'nullable|string|max:100',
            'gender'      => 'required|in:homme,femme,non_precise',
            'birth_date'  => 'nullable|date|before:today',
            'notes'       => 'nullable|string',
            'mesure_label'=> 'nullable|string|max:100',
            'mesures'     => 'nullable|array',
            'mesures.*'   => 'nullable',
        ]);

        $client = Client::create(\Arr::except($validated, ['mesure_label', 'mesures']));

        // Sauvegarde des mesures si au moins une valeur renseignée
        $mesuresData = collect($request->input('mesures', []))
            ->filter(fn($v) => $v !== null && $v !== '')
            ->toArray();

        if (!empty($mesuresData)) {
            // Séparer les champs legacy des champs JSON (f_*, h_*, e_*)
            $legacyKeys  = ['poitrine','taille','hanches','epaules','cou','bras',
                            'longueur_manche','longueur_robe','longueur_pantalon','entrejambe','notes'];
            $legacyFields = array_intersect_key($mesuresData, array_flip($legacyKeys));
            $jsonFields   = array_diff_key($mesuresData, array_flip($legacyKeys));

            $client->measurements()->create(array_merge(
                [
                    'label'      => $request->input('mesure_label', 'Mesures initiales'),
                    'is_default' => true,
                    'values'     => !empty($jsonFields) ? $jsonFields : null,
                ],
                $legacyFields
            ));
        }

        $msg = !empty($mesuresData) ? 'Client enregistré avec ses mesures.' : 'Client créé avec succès.';
        return redirect()->route('clients.show', $client)->with('success', $msg);
    }

    public function show(Client $client)
    {
        $client->load(['measurements','orders' => fn($q) => $q->latest()->limit(10), 'customOrders' => fn($q) => $q->latest()->limit(10)]);
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'phone'      => 'required|string|max:30',
            'email'      => 'nullable|email|max:150',
            'address'    => 'nullable|string',
            'city'       => 'nullable|string|max:100',
            'gender'     => 'required|in:homme,femme,non_precise',
            'birth_date' => 'nullable|date|before:today',
            'notes'      => 'nullable|string',
        ]);
        $client->update($validated);
        return redirect()->route('clients.show', $client)->with('success', 'Client mis à jour.');
    }

    public function alerts()
    {
        $today = Carbon::today();

        // Anniversaires dans les 7 prochains jours
        $birthdays = Client::whereNotNull('birth_date')
            ->get()
            ->filter(function($c) use ($today) {
                $bday = Carbon::parse($c->birth_date)->setYear($today->year);
                if ($bday->isPast() && !$bday->isToday()) {
                    $bday->addYear();
                }
                $c->days_until_birthday = $today->diffInDays($bday, false);
                $c->next_birthday = $bday;
                return $c->days_until_birthday >= 0 && $c->days_until_birthday <= 30;
            })
            ->sortBy('days_until_birthday')
            ->values();

        // Clients inactifs depuis plus de 90 jours (pas de commande récente)
        $inactiveThreshold = $today->copy()->subDays(90);
        $inactive = Client::with(['orders' => fn($q) => $q->latest()->limit(1),
                                  'customOrders' => fn($q) => $q->latest()->limit(1)])
            ->get()
            ->filter(function($c) use ($inactiveThreshold) {
                $lastOrder       = $c->orders->first()?->created_at;
                $lastCustomOrder = $c->customOrders->first()?->created_at;
                $lastActivity    = collect([$lastOrder, $lastCustomOrder])->filter()->max();

                if (!$lastActivity) {
                    // Jamais commandé
                    $c->last_activity = null;
                    $c->days_inactive = null;
                    return $c->orders_count === 0;
                }

                $c->last_activity = $lastActivity;
                $c->days_inactive = $lastActivity->diffInDays(now());
                return $lastActivity->lt($inactiveThreshold);
            })
            ->sortByDesc('days_inactive')
            ->values();

        // Meilleurs clients (top 5 par total dépensé)
        $topClients = Client::where('total_spent', '>', 0)
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();

        return view('clients.alerts', compact('birthdays', 'inactive', 'topClients'));
    }

    public function destroy(Client $client)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Accès réservé à l\'administrateur.');
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client supprimé.');
    }

    // Mesures
    public function measurements(Client $client)
    {
        return view('clients.measurements', compact('client'));
    }

    public function storeMeasurement(Request $request, Client $client)
    {
        $validated = $request->validate([
            'label'             => 'required|string|max:100',
            'poitrine'          => 'nullable|numeric|min:1',
            'taille'            => 'nullable|numeric|min:1',
            'hanches'           => 'nullable|numeric|min:1',
            'longueur_pantalon' => 'nullable|numeric|min:1',
            'longueur_manche'   => 'nullable|numeric|min:1',
            'longueur_robe'     => 'nullable|numeric|min:1',
            'cou'               => 'nullable|numeric|min:1',
            'epaules'           => 'nullable|numeric|min:1',
            'entrejambe'        => 'nullable|numeric|min:1',
            'bras'              => 'nullable|numeric|min:1',
            'notes'             => 'nullable|string',
            'is_default'        => 'boolean',
        ]);

        if (!empty($validated['is_default'])) {
            $client->measurements()->update(['is_default' => false]);
        }

        $client->measurements()->create($validated);
        return back()->with('success', 'Mesures enregistrées.');
    }

    public function updateMeasurement(Request $request, Client $client, Measurement $measurement)
    {
        $measurement->update($request->validated());
        return back()->with('success', 'Mesures mises à jour.');
    }

    public function destroyMeasurement(Client $client, Measurement $measurement)
    {
        $measurement->delete();
        return back()->with('success', 'Mesures supprimées.');
    }
}
