<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{CustomOrder, CustomOrderStatus, Client, Product, User, Measurement};
use App\Services\StockService;
use App\Services\FinanceService;
use App\Exports\CustomOrdersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class CustomOrderController extends Controller
{
    public function __construct(
        private StockService $stockService,
        private FinanceService $financeService
    ) {}

    public function index(Request $request)
    {
        $query = CustomOrder::with(['client','couturier','fabric'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('couturier')) {
            $query->where('assigned_to', $request->couturier);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($c) => $c->where('first_name', 'like', "%{$search}%")
                                                       ->orWhere('last_name', 'like', "%{$search}%")
                                                       ->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        $orders     = $query->paginate(15)->withQueryString();
        $couturiers = User::where('role', 'couturier')->where('is_active', true)->get();
        $statuses   = CustomOrder::STATUSES;

        return view('orders.custom.index', compact('orders','couturiers','statuses'));
    }

    public function create()
    {
        $clients    = Client::orderBy('first_name')->get();
        $couturiers = User::where('role', 'couturier')->where('is_active', true)->get();
        $fabrics    = Product::active()->tissus()->get();
        $garmentTypes = [
            'robe'      => 'Robe',
            'costume'   => 'Costume',
            'pantalon'  => 'Pantalon',
            'chemise'   => 'Chemise',
            'boubou'    => 'Boubou',
            'ensemble'  => 'Ensemble',
            'autre'     => 'Autre',
        ];
        return view('orders.custom.create', compact('clients','couturiers','fabrics','garmentTypes'));
    }

    public function store(Request $request)
    {
        $isGroup = $request->boolean('is_group_order');

        // ── Règles de validation selon le type de commande ──────────────
        $rules = [
            'client_id'          => 'required|exists:clients,id',
            'model_description'  => 'nullable|string',
            'fabric_product_id'  => 'nullable|exists:products,id',
            'fabric_meters'      => 'nullable|numeric|min:0.5',
            'fabric_color'       => 'nullable|string',
            'accessories'        => 'nullable|array',
            'deposit'            => 'nullable|numeric|min:0',
            'payment_method'     => 'nullable|in:cash,mobile_money,card,credit',
            'delivery_date'      => 'nullable|date|after:today',
            'assigned_to'        => 'nullable|exists:users,id',
            'notes'              => 'nullable|string',
        ];

        if ($isGroup) {
            $rules = array_merge($rules, [
                'group_name'         => 'required|string|max:255',
                'group_occasion'     => 'nullable|string|max:255',
                'group_members'      => 'required|string', // JSON encodé
                'labor_cost'         => 'nullable|numeric|min:0',
                'model_photos.*'     => 'nullable|image|max:5120',
            ]);
        } else {
            $rules = array_merge($rules, [
                'gender'                => 'required|in:homme,femme,enfant',
                'garment_type'          => 'required|string',
                'model_name'            => 'nullable|string|max:255',
                'model_photo'           => 'nullable|image|max:2048',
                'labor_cost'            => 'required|numeric|min:0',
                'measurement_id'        => 'nullable|exists:measurements,id',
                'new_measurement_label' => 'nullable|string|max:255',
                'new_measurement'       => 'nullable|array',
                'manual_measurement'    => 'nullable|array',
            ]);
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $request, $isGroup) {

            // ── Coût tissu ────────────────────────────────────────────────
            $fabricCost = 0;
            if (!empty($validated['fabric_product_id']) && !empty($validated['fabric_meters'])) {
                $fabric     = Product::find($validated['fabric_product_id']);
                $fabricCost = $fabric->price_per_meter * $validated['fabric_meters'];
            }

            // ── Coût accessoires ─────────────────────────────────────────
            $accessoriesCost = 0;
            if (!empty($validated['accessories'])) {
                foreach ($validated['accessories'] as $acc) {
                    $accessoriesCost += ($acc['price'] ?? 0) * ($acc['qty'] ?? 1);
                }
            }

            $deposit = $validated['deposit'] ?? 0;

            // ════════════════════════════════════════════════════════════
            // CAS GROUPE
            // ════════════════════════════════════════════════════════════
            if ($isGroup) {

                // Décoder les membres (envoyés en JSON)
                $groupMembers = json_decode($request->input('group_members', '[]'), true) ?? [];

                // Calcul main d'œuvre agrégée depuis les vêtements de chaque membre
                $laborCost = 0;
                foreach ($groupMembers as $member) {
                    foreach ($member['garments'] ?? [] as $garment) {
                        $laborCost += ($garment['labor_cost'] ?? 0) * ($garment['qty'] ?? 1);
                    }
                }
                // MO globale saisie en plus (tissu, finitions)
                $laborCost += ($validated['labor_cost'] ?? 0);

                $total = $fabricCost + $laborCost + $accessoriesCost;

                $order = CustomOrder::create([
                    'client_id'         => $validated['client_id'],
                    'is_group_order'    => true,
                    'group_name'        => $validated['group_name'],
                    'group_occasion'    => $validated['group_occasion'] ?? null,
                    'group_members'     => $groupMembers,
                    'model_description' => $validated['model_description'] ?? null,
                    'fabric_product_id' => $validated['fabric_product_id'] ?? null,
                    'fabric_meters'     => $validated['fabric_meters'] ?? null,
                    'fabric_color'      => $validated['fabric_color'] ?? null,
                    'fabric_cost'       => $fabricCost,
                    'labor_cost'        => $laborCost,
                    'accessories'       => $validated['accessories'] ?? null,
                    'accessories_cost'  => $accessoriesCost,
                    'total'             => $total,
                    'deposit'           => $deposit,
                    'amount_paid'       => $deposit,
                    'payment_method'    => $validated['payment_method'] ?? null,
                    'payment_status'    => $deposit >= $total ? 'paid' : ($deposit > 0 ? 'partial' : 'unpaid'),
                    'delivery_date'     => $validated['delivery_date'] ?? null,
                    'assigned_to'       => $validated['assigned_to'] ?? null,
                    'cashier_id'        => auth()->id(),
                    'status'            => 'recu',
                    'notes'             => $validated['notes'] ?? null,
                    // Champs individuels non applicables au groupe
                    'gender'        => null,
                    'garment_type'  => null,
                ]);

                // Upload photos multiples du modèle (jusqu'à 8)
                if ($request->hasFile('model_photos')) {
                    $paths = [];
                    foreach ($request->file('model_photos') as $photo) {
                        $paths[] = $photo->store('custom_orders/models', 'public');
                    }
                    $order->update(['model_photos' => $paths]);
                }

            // ════════════════════════════════════════════════════════════
            // CAS INDIVIDUEL (comportement original)
            // ════════════════════════════════════════════════════════════
            } else {

                $laborCost = $validated['labor_cost'];
                $total     = $fabricCost + $laborCost + $accessoriesCost;

                // Gestion des mesures
                $measurementId = $validated['measurement_id'] ?? null;

                $newMeasurementData = array_filter(
                    $request->input('new_measurement', []),
                    fn($v) => $v !== null && $v !== ''
                );
                $newLabel = $request->input('new_measurement_label');

                if ($newLabel && !empty($newMeasurementData)) {
                    $measurement = Measurement::create([
                        'client_id'  => $validated['client_id'],
                        'label'      => $newLabel,
                        'values'     => $newMeasurementData,
                        'is_default' => $request->boolean('new_measurement_default'),
                    ]);
                    $measurementId = $measurement->id;
                }

                $manualData = array_filter(
                    $request->input('manual_measurement', []),
                    fn($v) => $v !== null && $v !== ''
                );
                if (empty($measurementId) && !empty($manualData)) {
                    $measurement = Measurement::create([
                        'client_id'  => $validated['client_id'],
                        'label'      => 'Saisie directe — ' . now()->format('d/m/Y'),
                        'values'     => $manualData,
                        'is_default' => false,
                    ]);
                    $measurementId = $measurement->id;
                }

                $order = CustomOrder::create([
                    ...$validated,
                    'is_group_order'    => false,
                    'measurement_id'    => $measurementId,
                    'fabric_cost'       => $fabricCost,
                    'accessories_cost'  => $accessoriesCost,
                    'total'             => $total,
                    'amount_paid'       => $deposit,
                    'cashier_id'        => auth()->id(),
                    'status'            => 'recu',
                    'payment_status'    => $deposit >= $total ? 'paid' : ($deposit > 0 ? 'partial' : 'unpaid'),
                ]);

                // Upload photo modèle unique
                if ($request->hasFile('model_photo')) {
                    $path = $request->file('model_photo')->store('custom_orders/models', 'public');
                    $order->update(['model_photo' => $path]);
                }
            }

            // ── Déduire tissu du stock (commun aux deux modes) ───────────
            if (!empty($validated['fabric_product_id']) && !empty($validated['fabric_meters'])) {
                $this->stockService->deduct(
                    $validated['fabric_product_id'],
                    $validated['fabric_meters'],
                    "Commande sur mesure {$order->reference}",
                    CustomOrder::class,
                    $order->id
                );
            }

            // ── Log statut initial ───────────────────────────────────────
            CustomOrderStatus::create([
                'custom_order_id' => $order->id,
                'user_id'         => auth()->id(),
                'status'          => 'recu',
                'comment'         => $isGroup
                    ? "Commande groupe créée — {$order->group_name}"
                    : 'Commande créée',
            ]);
        });

        return redirect()->route('custom-orders.index')
            ->with('success', 'Commande sur mesure créée avec succès.');
    }

    public function show(CustomOrder $customOrder)
    {
        $customOrder->load(['client.measurements','couturier','fabric','statusHistory.user','payments.cashier','delivery.driver']);
        $couturiers = User::where('role', 'couturier')->where('is_active', true)->get();
        return view('orders.custom.show', compact('customOrder','couturiers'));
    }

    public function updateStatus(Request $request, CustomOrder $customOrder)
    {
        $request->validate([
            'status'  => 'required|in:' . implode(',', array_keys(CustomOrder::STATUSES)),
            'comment' => 'nullable|string|max:500',
        ]);

        $oldStatus = $customOrder->status;
        $newStatus = $request->status;

        $timestamps = [];
        if ($newStatus === 'en_couture' && !$customOrder->started_at) {
            $timestamps['started_at'] = now();
        }
        if ($newStatus === 'pret' && !$customOrder->completed_at) {
            $timestamps['completed_at'] = now();
        }
        if ($newStatus === 'livre' && !$customOrder->delivered_at) {
            $timestamps['delivered_at'] = now();
        }

        $customOrder->update(array_merge(['status' => $newStatus], $timestamps));

        CustomOrderStatus::create([
            'custom_order_id' => $customOrder->id,
            'user_id'         => auth()->id(),
            'status'          => $newStatus,
            'comment'         => $request->comment,
        ]);

        return back()->with('success', "Statut mis à jour : {$customOrder->getStatusLabel()}");
    }

    public function assignCouturier(Request $request, CustomOrder $customOrder)
    {
        $request->validate(['couturier_id' => 'required|exists:users,id']);
        $customOrder->update(['assigned_to' => $request->couturier_id]);
        return back()->with('success', 'Couturier assigné avec succès.');
    }

    public function edit(CustomOrder $customOrder)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Accès réservé à l\'administrateur.');
        $clients    = Client::orderBy('first_name')->get();
        $couturiers = User::where('role', 'couturier')->get();
        $fabrics    = Product::active()->tissus()->get();
        return view('orders.custom.edit', compact('customOrder','clients','couturiers','fabrics'));
    }

    public function update(Request $request, CustomOrder $customOrder)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Accès réservé à l\'administrateur.');
        $validated = $request->validate([
            'client_id'             => 'required|exists:clients,id',
            'gender'                => 'required|in:homme,femme,enfant',
            'garment_type'          => 'required|string',
            'model_name'            => 'nullable|string|max:255',
            'model_description'     => 'nullable|string',
            'model_photo'           => 'nullable|image|max:2048',
            'fabric_product_id'     => 'nullable|exists:products,id',
            'fabric_meters'         => 'nullable|numeric|min:0.5',
            'fabric_color'          => 'nullable|string',
            'labor_cost'            => 'required|numeric|min:0',
            'deposit'               => 'nullable|numeric|min:0',
            'payment_method'        => 'nullable|in:cash,mobile_money,card,credit',
            'delivery_date'         => 'nullable|date',
            'assigned_to'           => 'nullable|exists:users,id',
            'measurement_id'        => 'nullable|exists:measurements,id',
            'new_measurement_label' => 'nullable|string|max:255',
            'new_measurement'       => 'nullable|array',
            'notes'                 => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request, $customOrder) {

            // Recalcul coûts tissu
            $fabricCost = 0;
            if (!empty($validated['fabric_product_id']) && !empty($validated['fabric_meters'])) {
                $fabric = Product::find($validated['fabric_product_id']);
                $fabricCost = $fabric->price_per_meter * $validated['fabric_meters'];
            }

            $total   = $fabricCost + ($validated['labor_cost'] ?? 0);
            $deposit = $validated['deposit'] ?? $customOrder->deposit ?? 0;

            $measurementId = $validated['measurement_id'] ?? null;

            // Créer une nouvelle fiche si demandé
            if (!empty($validated['new_measurement_label']) && !empty($validated['new_measurement'])) {
                $measurement = \App\Models\Measurement::create([
                    'client_id'  => $validated['client_id'],
                    'label'      => $validated['new_measurement_label'],
                    'values'     => array_filter($validated['new_measurement'], fn($v) => $v !== null && $v !== ''),
                    'is_default' => $request->boolean('new_measurement_default'),
                ]);
                $measurementId = $measurement->id;
            }

            $customOrder->update([
                'client_id'         => $validated['client_id'],
                'gender'            => $validated['gender'],
                'garment_type'      => $validated['garment_type'],
                'model_name'        => $validated['model_name'] ?? null,
                'model_description' => $validated['model_description'] ?? null,
                'fabric_product_id' => $validated['fabric_product_id'] ?? null,
                'fabric_meters'     => $validated['fabric_meters'] ?? null,
                'fabric_color'      => $validated['fabric_color'] ?? null,
                'fabric_cost'       => $fabricCost,
                'labor_cost'        => $validated['labor_cost'],
                'total'             => $total,
                'deposit'           => $deposit,
                'amount_paid'       => $deposit,
                'payment_method'    => $validated['payment_method'] ?? $customOrder->payment_method,
                'payment_status'    => $deposit >= $total ? 'paid' : ($deposit > 0 ? 'partial' : 'unpaid'),
                'delivery_date'     => $validated['delivery_date'] ?? null,
                'assigned_to'       => $validated['assigned_to'] ?? null,
                'measurement_id'    => $measurementId,
                'notes'             => $validated['notes'] ?? null,
            ]);

            // Upload nouvelle photo si fournie
            if ($request->hasFile('model_photo')) {
                $path = $request->file('model_photo')->store('custom_orders/models', 'public');
                $customOrder->update(['model_photo' => $path]);
            }
        });

        return redirect()->route('custom-orders.show', $customOrder)
            ->with('success', 'Commande mise à jour avec succès.');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['search', 'status', 'couturier']);
        $format  = $request->get('format', 'excel');

        if ($format === 'pdf') {
            $query = CustomOrder::with(['client','couturier'])->latest();
            if (!empty($filters['search'])) {
                $s = $filters['search'];
                $query->where(function($q) use ($s) {
                    $q->where('reference','like',"%$s%")
                      ->orWhereHas('client', fn($c) =>
                          $c->where('first_name','like',"%$s%")
                            ->orWhere('last_name','like',"%$s%")
                            ->orWhere('phone','like',"%$s%")
                      );
                });
            }
            if (!empty($filters['status']))    $query->where('status', $filters['status']);
            if (!empty($filters['couturier'])) $query->where('assigned_to', $filters['couturier']);
            $orders = $query->get();
            $pdf = Pdf::loadView('custom-orders.export-pdf', compact('orders'))
                      ->setPaper('a4', 'landscape');
            return $pdf->download('commandes-sur-mesure_' . now()->format('Ymd_Hi') . '.pdf');
        }

        $filename = 'commandes-sur-mesure_' . now()->format('Ymd_Hi') . '.xlsx';
        return Excel::download(new CustomOrdersExport($filters), $filename);
    }

    public function saveMeasures(Request $request, CustomOrder $customOrder)
    {
        // Accepter aussi bien 'measures' que 'new_measurement' ou 'manual_measurement'
        $measures = $request->input('measures', []);
        if (empty($measures)) {
            $measures = $request->input('new_measurement', []);
        }
        if (empty($measures)) {
            $measures = $request->input('manual_measurement', []);
        }

        $measures = array_filter($measures, fn($v) => $v !== null && $v !== '');

        if (empty($measures)) {
            return back()->with('error', 'Aucune mesure à enregistrer.');
        }

        if ($customOrder->measurement_id && $customOrder->measurement) {
            // Mettre à jour la fiche existante — fusionner avec les anciennes valeurs
            $existing = $customOrder->measurement->values ?? [];
            $customOrder->measurement->update(['values' => array_merge($existing, $measures)]);
        } else {
            // Créer une nouvelle fiche et l'associer
            $measurement = \App\Models\Measurement::create([
                'client_id'  => $customOrder->client_id,
                'label'      => $request->input('measurement_label', 'Fiche — ' . $customOrder->reference),
                'values'     => $measures,
                'is_default' => $request->boolean('is_default', false),
            ]);
            $customOrder->update(['measurement_id' => $measurement->id]);
        }

        return redirect()->route('custom-orders.fiche', $customOrder)
            ->with('measures_saved', true);
    }

    public function recordPayment(Request $request, CustomOrder $customOrder)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $customOrder->balance,
            'method' => 'required|in:cash,mobile_money,card,credit',
        ]);

        $this->financeService->recordPayment(
            CustomOrder::class,
            $customOrder->id,
            $customOrder->client_id,
            $request->amount,
            $request->method
        );

        return back()->with('success', 'Paiement enregistré avec succès.');
    }

    public function printFiche(CustomOrder $customOrder, Request $request)
    {
        $customOrder->load(['client', 'couturier', 'cashier', 'fabric', 'statusHistory.user']);

        // Téléchargement PDF
        if ($request->boolean('download')) {
            $pdf = Pdf::loadView('custom-orders.fiche', compact('customOrder'))
                      ->setPaper('a4', 'portrait');
            return $pdf->download('fiche-atelier-' . $customOrder->reference . '.pdf');
        }

        // Affichage HTML dans le navigateur (avec bouton Imprimer)
        return view('custom-orders.fiche', compact('customOrder'));
    }

    public function destroy(CustomOrder $customOrder)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Accès réservé à l\'administrateur.');
        if (in_array($customOrder->status, ['en_couture','finition','controle_qualite'])) {
            return back()->with('error', 'Impossible de supprimer une commande en cours de production.');
        }
        $customOrder->delete(); // soft delete → va dans la corbeille
        return redirect()->route('custom-orders.index')
            ->with('success', '🗑 Commande déplacée dans la corbeille. Vous pouvez la restaurer depuis la corbeille.');
    }

    // ── CORBEILLE ────────────────────────────────────────────
    public function corbeille()
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $orders = CustomOrder::onlyTrashed()
            ->with(['client','couturier'])
            ->latest('deleted_at')
            ->paginate(20);
        return view('orders.custom.corbeille', compact('orders'));
    }

    public function restaurer(int $id)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $order = CustomOrder::onlyTrashed()->findOrFail($id);
        $order->restore();
        return back()->with('success', '✅ Commande ' . $order->reference . ' restaurée avec succès.');
    }

    public function purger(int $id)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $order = CustomOrder::onlyTrashed()->findOrFail($id);
        $order->statuses()->delete();
        $order->forceDelete(); // suppression définitive
        return back()->with('success', '🗑 Commande supprimée définitivement.');
    }
}
