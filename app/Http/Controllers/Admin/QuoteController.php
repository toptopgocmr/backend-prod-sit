<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Quote, CustomOrder, Client, Product, User, Measurement};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Barryvdh\DomPDF\Facade\Pdf;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Quote::with(['client', 'creator'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%{$s}%")
                  ->orWhereHas('client', fn($c) =>
                      $c->where('first_name', 'like', "%{$s}%")
                        ->orWhere('last_name', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%")
                  );
            });
        }

        // Marquer automatiquement les devis expirés
        Quote::where('status', 'envoye')
             ->whereDate('valid_until', '<', now())
             ->update(['status' => 'expire']);

        $quotes   = $query->paginate(15)->withQueryString();
        $statuses = Quote::STATUSES;

        return view('orders.quotes.index', compact('quotes', 'statuses'));
    }

    public function create()
    {
        $clients          = Client::orderBy('first_name')->get();
        $fabrics          = Product::active()->tissus()->get();
        $accessoryProducts = Product::active()->accessoires()->orderBy('name')->get();
        $garmentTypes = [
            'robe'    => 'Robe',    'costume'  => 'Costume',
            'pantalon'=> 'Pantalon','chemise'  => 'Chemise',
            'boubou'  => 'Boubou', 'ensemble' => 'Ensemble',
            'autre'   => 'Autre',
        ];
        return view('orders.quotes.create', compact('clients', 'fabrics', 'accessoryProducts', 'garmentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'             => 'required|exists:clients,id',
            'gender'                => 'nullable|in:homme,femme,enfant',
            // Garments (multi-vêtements)
            'garments'              => 'required|array|min:1',
            'garments.*.garment_type'    => 'nullable|string|max:100',
            'garments.*.garment_type_entries'              => 'nullable|array',
            'garments.*.garment_type_entries.*.value'      => 'nullable|string|max:100',
            'garments.*.garment_type_entries.*.price'      => 'nullable|numeric|min:0',
            'garments.*.model_name'      => 'nullable|string|max:255',
            'garments.*.model_description' => 'nullable|string',
            'garments.*.qty'             => 'nullable|integer|min:1',
            // Tissus de chaque vêtement
            'garments.*.fabrics'              => 'nullable|array',
            'garments.*.fabrics.*.mode'       => 'nullable|in:stock,custom',
            'garments.*.fabrics.*.fabric_product_id' => 'nullable|exists:products,id',
            'garments.*.fabrics.*.fabric_name'        => 'nullable|string|max:255',
            'garments.*.fabrics.*.fabric_price_per_meter' => 'nullable|numeric|min:0',
            'garments.*.fabrics.*.fabric_meters'      => 'nullable|numeric|min:0',
            'garments.*.fabrics.*.fabric_color'       => 'nullable|string|max:255',
            // Accessoires communs au devis
            'accessories'                  => 'nullable|array',
            'accessories.*.mode'           => 'nullable|in:stock,custom',
            'accessories.*.product_id'     => 'nullable|exists:products,id',
            'accessories.*.name'           => 'nullable|string|max:255',
            'accessories.*.qty'            => 'nullable|integer|min:1',
            'accessories.*.price'          => 'nullable|numeric|min:0',
            // Coût main d'œuvre global
            'labor_cost'            => 'required|numeric|min:0',
            'valid_until'           => 'nullable|date|after:today',
            'delivery_date'         => 'nullable|date',
            'notes'                 => 'nullable|string',
        ]);

        try {
        DB::transaction(function () use ($validated, $request) {

            // ── Calcul du coût tissu total (tous vêtements confondus) ──
            $fabricCostTotal      = 0;
            $garmentTypeCostTotal = 0;
            $garmentsData         = [];

            foreach ($validated['garments'] as $garment) {
                $garmentFabrics    = [];
                $garmentFabricCost = 0;

                // ── Types de vêtement (saisie manuelle = prix supplémentaire possible) ──
                $garmentTypeEntries = [];
                $garmentTypeCost    = 0;
                foreach ($garment['garment_type_entries'] ?? [] as $entry) {
                    $value = trim($entry['value'] ?? '');
                    if ($value === '') continue;
                    $price = floatval($entry['price'] ?? 0);
                    $garmentTypeCost += $price;
                    $garmentTypeEntries[] = ['value' => $value, 'price' => $price];
                }
                $garmentTypeCostTotal += $garmentTypeCost;

                foreach ($garment['fabrics'] ?? [] as $fabric) {
                    $cost = 0;
                    $fabricProductId      = null;
                    $fabricName           = null;
                    $fabricPricePerMeter  = null;

                    if (($fabric['mode'] ?? 'custom') === 'stock' && !empty($fabric['fabric_product_id'])) {
                        $product             = Product::find($fabric['fabric_product_id']);
                        $fabricProductId     = $product?->id;
                        $meters              = floatval($fabric['fabric_meters'] ?? 0);
                        $cost                = ($product?->price_per_meter ?? 0) * $meters;
                        $fabricPricePerMeter = $product?->price_per_meter ?? 0;
                    } elseif (!empty($fabric['fabric_name']) && !empty($fabric['fabric_meters'])) {
                        $fabricName          = $fabric['fabric_name'];
                        $fabricPricePerMeter = floatval($fabric['fabric_price_per_meter'] ?? 0);
                        $meters              = floatval($fabric['fabric_meters']);
                        $cost                = $fabricPricePerMeter * $meters;
                    }

                    $garmentFabricCost += $cost;

                    $garmentFabrics[] = [
                        'mode'                  => $fabric['mode'] ?? 'custom',
                        'fabric_product_id'     => $fabricProductId,
                        'fabric_name'           => $fabricName,
                        'fabric_price_per_meter'=> $fabricPricePerMeter,
                        'fabric_meters'         => floatval($fabric['fabric_meters'] ?? 0),
                        'fabric_color'          => $fabric['fabric_color'] ?? null,
                        'fabric_cost'           => $cost,
                    ];
                }

                $fabricCostTotal += $garmentFabricCost;

                $garmentsData[] = [
                    'garment_type'         => $garment['garment_type'] ?? null,
                    'garment_type_entries' => $garmentTypeEntries,
                    'garment_type_cost'    => $garmentTypeCost,
                    'model_name'           => $garment['model_name'] ?? null,
                    'model_description'    => $garment['model_description'] ?? null,
                    'qty'                  => intval($garment['qty'] ?? 1),
                    'fabrics'              => $garmentFabrics,
                    'fabric_cost'          => $garmentFabricCost,
                ];
            }

            // ── Accessoires ──
            $accessoriesCost = 0;
            $accessoriesData = [];
            if (!empty($validated['accessories'])) {
                foreach ($validated['accessories'] as $acc) {
                    $price = floatval($acc['price'] ?? 0);
                    $qty   = intval($acc['qty'] ?? 1);
                    $name  = $acc['name'] ?? '';
                    $mode  = $acc['mode'] ?? 'custom';

                    // Si mode stock, récupérer le prix et le nom depuis le produit
                    if ($mode === 'stock' && !empty($acc['product_id'])) {
                        $product = Product::find($acc['product_id']);
                        if ($product) {
                            $price = $product->price ?? 0;
                            $name  = $product->name;
                        }
                    }

                    $accessoriesCost += $price * $qty;
                    $accessoriesData[] = [
                        'mode'       => $mode,
                        'product_id' => $acc['product_id'] ?? null,
                        'name'       => $name,
                        'qty'        => $qty,
                        'price'      => $price,
                    ];
                }
            }

            $total = $fabricCostTotal + $garmentTypeCostTotal + floatval($validated['labor_cost']) + $accessoriesCost;

            Quote::create([
                'client_id'        => $validated['client_id'],
                'gender'           => $validated['gender'] ?? null,
                'garments'         => $garmentsData,
                'fabric_cost'      => $fabricCostTotal,
                'accessories'      => $accessoriesData,
                'accessories_cost' => $accessoriesCost,
                'labor_cost'       => $validated['labor_cost'],
                'total'            => $total,
                'created_by'       => auth()->id(),
                'status'           => 'brouillon',
                'valid_until'      => $validated['valid_until'] ?? null,
                'delivery_date'    => $validated['delivery_date'] ?? null,
                'notes'            => $validated['notes'] ?? null,
            ]);
        });
        } catch (Throwable $e) {
            Log::error('Erreur lors de la création du devis : ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => auth()->id(),
            ]);

            return back()->withInput()->with('error',
                "Impossible d'enregistrer le devis. Vérifiez les tissus, accessoires et le coût de confection saisis, puis réessayez. Si le problème persiste, contactez l'administrateur."
            );
        }

        return redirect()->route('quotes.index')->with('success', 'Devis créé avec succès.');
    }

    public function show(Quote $quote)
    {
        $quote->load(['client', 'creator', 'fabric', 'customOrder']);
        return view('orders.quotes.show', compact('quote'));
    }

    public function updateStatus(Request $request, Quote $quote)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Quote::STATUSES)),
        ]);
        $quote->update(['status' => $request->status]);
        return back()->with('success', "Statut du devis mis à jour : {$quote->getStatusLabel()}");
    }

    /**
     * Convertir un devis accepté en commande sur mesure.
     * Pour les devis multi-vêtements, on prend le premier vêtement comme référence.
     */
    public function convertToOrder(Quote $quote)
    {
        if ($quote->status !== 'accepte') {
            return back()->with('error', 'Seul un devis accepté peut être converti en commande.');
        }
        if ($quote->custom_order_id) {
            return redirect()->route('custom-orders.show', $quote->custom_order_id)
                ->with('info', 'Ce devis a déjà été converti en commande.');
        }

        $order = DB::transaction(function () use ($quote) {
            // Données du premier vêtement pour remplir CustomOrder (legacy)
            $firstGarment = $quote->garments[0] ?? [];
            $firstFabric  = $firstGarment['fabrics'][0] ?? [];

            $order = CustomOrder::create([
                'client_id'         => $quote->client_id,
                'gender'            => $quote->gender ?? 'femme',
                'garment_type'      => $firstGarment['garment_type'] ?? 'autre',
                'model_name'        => $firstGarment['model_name'] ?? $quote->model_name,
                'model_description' => $firstGarment['model_description'] ?? $quote->model_description,
                'model_photo'       => $quote->model_photo,
                'fabric_product_id' => $firstFabric['fabric_product_id'] ?? $quote->fabric_product_id,
                'fabric_meters'     => $firstFabric['fabric_meters'] ?? $quote->fabric_meters,
                'fabric_color'      => $firstFabric['fabric_color'] ?? $quote->fabric_color,
                'fabric_cost'       => $quote->fabric_cost,
                'labor_cost'        => $quote->labor_cost,
                'accessories'       => $quote->accessories,
                'accessories_cost'  => $quote->accessories_cost,
                'total'             => $quote->total,
                'amount_paid'       => 0,
                'deposit'           => 0,
                'cashier_id'        => auth()->id(),
                'delivery_date'     => $quote->delivery_date,
                'notes'             => $quote->notes,
                'status'            => 'recu',
                'payment_status'    => 'unpaid',
            ]);

            \App\Models\CustomOrderStatus::create([
                'custom_order_id' => $order->id,
                'user_id'         => auth()->id(),
                'status'          => 'recu',
                'comment'         => "Converti depuis le devis {$quote->reference}",
            ]);

            $quote->update(['custom_order_id' => $order->id]);

            return $order;
        });

        return redirect()->route('custom-orders.show', $order)
            ->with('success', "Devis converti en commande {$order->reference}.");
    }

    public function pdf(Quote $quote)
    {
        $quote->load(['client', 'creator', 'fabric']);
        $pdf = Pdf::loadView('orders.quotes.pdf', compact('quote'))
                  ->setPaper('a4', 'portrait');
        return $pdf->download("devis-{$quote->reference}.pdf");
    }

    public function destroy(Quote $quote)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Accès réservé à l\'administrateur.');
        $quote->delete();
        return redirect()->route('quotes.index')->with('success', 'Devis supprimé.');
    }
}
