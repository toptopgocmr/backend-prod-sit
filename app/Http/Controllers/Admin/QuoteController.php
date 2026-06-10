<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Quote, CustomOrder, Client, Product, User, Measurement};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $clients      = Client::orderBy('first_name')->get();
        $fabrics      = Product::active()->tissus()->get();
        $garmentTypes = [
            'robe'    => 'Robe',    'costume'  => 'Costume',
            'pantalon'=> 'Pantalon','chemise'  => 'Chemise',
            'boubou'  => 'Boubou', 'ensemble' => 'Ensemble',
            'autre'   => 'Autre',
        ];
        return view('orders.quotes.create', compact('clients', 'fabrics', 'garmentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'         => 'required|exists:clients,id',
            'gender'            => 'nullable|in:homme,femme,enfant',
            'garment_type'      => 'nullable|string',
            'model_name'        => 'nullable|string|max:255',
            'model_description' => 'nullable|string',
            'model_photo'       => 'nullable|image|max:2048',
            'fabric_product_id' => 'nullable|exists:products,id',
            'fabric_meters'     => 'nullable|numeric|min:0',
            'fabric_color'      => 'nullable|string',
            'accessories'       => 'nullable|array',
            'labor_cost'        => 'required|numeric|min:0',
            'valid_until'       => 'nullable|date|after:today',
            'delivery_date'     => 'nullable|date',
            'notes'             => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $fabricCost = 0;
            if (!empty($validated['fabric_product_id']) && !empty($validated['fabric_meters'])) {
                $fabric = Product::find($validated['fabric_product_id']);
                $fabricCost = $fabric->price_per_meter * $validated['fabric_meters'];
            }

            $accessoriesCost = 0;
            if (!empty($validated['accessories'])) {
                foreach ($validated['accessories'] as $acc) {
                    $accessoriesCost += ($acc['price'] ?? 0) * ($acc['qty'] ?? 1);
                }
            }

            $total = $fabricCost + $validated['labor_cost'] + $accessoriesCost;

            $quote = Quote::create([
                ...$validated,
                'fabric_cost'      => $fabricCost,
                'accessories_cost' => $accessoriesCost,
                'total'            => $total,
                'created_by'       => auth()->id(),
                'status'           => 'brouillon',
            ]);

            if ($request->hasFile('model_photo')) {
                $path = $request->file('model_photo')->store('quotes/models', 'public');
                $quote->update(['model_photo' => $path]);
            }
        });

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
            $order = CustomOrder::create([
                'client_id'         => $quote->client_id,
                'gender'            => $quote->gender ?? 'femme',
                'garment_type'      => $quote->garment_type ?? 'autre',
                'model_name'        => $quote->model_name,
                'model_description' => $quote->model_description,
                'model_photo'       => $quote->model_photo,
                'fabric_product_id' => $quote->fabric_product_id,
                'fabric_meters'     => $quote->fabric_meters,
                'fabric_color'      => $quote->fabric_color,
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

    /**
     * Télécharger le devis en PDF.
     */
    public function pdf(Quote $quote)
    {
        $quote->load(['client', 'creator', 'fabric']);
        $pdf = Pdf::loadView('orders.quotes.pdf', compact('quote'))
                  ->setPaper('a4', 'portrait');
        return $pdf->download("devis-{$quote->reference}.pdf");
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();
        return redirect()->route('quotes.index')->with('success', 'Devis supprimé.');
    }
}
