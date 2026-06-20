<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Order, OrderItem, Client, Product};
use App\Services\{StockService, FinanceService};
use App\Exports\OrdersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function __construct(
        private StockService $stockService,
        private FinanceService $financeService
    ) {}

    public function index(Request $request)
    {
        $query = Order::with(['client','cashier'])->latest();

        if ($request->filled('status'))         $query->where('status', $request->status);
        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'not_paid') {
                $query->whereIn('payment_status', ['unpaid', 'partial']);
            } else {
                $query->where('payment_status', $request->payment_status);
            }
        }
        if ($request->filled('type'))           $query->where('type', $request->type);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('reference', 'like', "%$s%")
                  ->orWhereHas('client', fn($c) =>
                      $c->where('first_name','like',"%$s%")
                        ->orWhere('last_name','like',"%$s%")
                        ->orWhere('phone','like',"%$s%")
                  );
            });
        }

        $orders = $query->paginate(15)->withQueryString();
        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $clients  = Client::orderBy('first_name')->get();
        $products = Product::active()->with('category')->orderBy('type')->orderBy('name')->get();
        $fabrics  = Product::active()->where('type', 'tissu')->orderBy('name')->get();
        $garmentTypes = [
            'chemise'   => 'Chemise',
            'pantalon'  => 'Pantalon',
            'costume'   => 'Costume',
            'robe'      => 'Robe',
            'jupe'      => 'Jupe',
            'veste'     => 'Veste',
            'boubou'    => 'Boubou',
            'ensemble'  => 'Ensemble',
            'autre'     => 'Autre',
        ];
        $couturiers = \App\Models\User::where('role', 'couturier')->orderBy('name')->get();
        return view('orders.create', compact('clients','products','fabrics','garmentTypes','couturiers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id'      => 'required|exists:clients,id',
            'type'           => 'required|in:tissu,pret_a_porter,mixte',
            'items'          => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount'       => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,mobile_money,card,credit',
            'amount_paid'    => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $subtotal = 0;
            foreach ($request->items as $item) {
                $lineBase     = $item['unit_price'] * $item['quantity'];
                $lineDiscount = $lineBase * ($item['discount'] ?? 0) / 100;
                $subtotal    += $lineBase - $lineDiscount;
            }
            $discount = $request->discount ?? 0;
            $total    = $subtotal - $discount;
            $paid     = min($request->amount_paid ?? 0, $total);

            $order = Order::create([
                'client_id'      => $request->client_id,
                'cashier_id'     => auth()->id(),
                'type'           => $request->type,
                'status'         => 'confirmed',
                'payment_status' => $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                'payment_method' => $request->payment_method,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'amount_paid'    => $paid,
                'notes'          => $request->notes,
                'confirmed_at'   => now(),
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $discountPct = $item['discount'] ?? 0;
                $lineBase    = $item['unit_price'] * $item['quantity'];
                $lineDiscount = $lineBase * $discountPct / 100;
                $itemTotal   = $lineBase - $lineDiscount;

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'unit_price'   => $item['unit_price'],
                    'quantity'     => $item['quantity'],
                    'unit'         => $product->getStockUnit(),
                    'discount'     => $lineDiscount,
                    'total'        => $itemTotal,
                ]);

                // Déduire stock
                $this->stockService->deduct(
                    $product->id,
                    $item['quantity'],
                    "Vente {$order->reference}",
                    Order::class,
                    $order->id
                );
            }

            // Mettre à jour stats client
            $order->client->increment('orders_count');
            $order->client->increment('total_spent', $paid);
        });

        return redirect()->route('orders.index')->with('success', 'Vente enregistrée avec succès.');
    }

    public function show(Order $order)
    {
        $order->load([
            'client'    => fn($q) => $q->withTrashed(),
            'cashier'   => fn($q) => $q->withTrashed(),
            'items.product' => fn($q) => $q->withTrashed(),
            'payments',
            'delivery',
        ]);
        return view('orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        if ($order->status === 'delivered') {
            return back()->with('error', 'Impossible de modifier une commande livrée.');
        }
        $clients  = Client::orderBy('first_name')->get();
        $products = Product::active()->get();
        return view('orders.edit', compact('order','clients','products'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|in:pending,confirmed,processing,ready,delivered,cancelled']);
        $order->update(['status' => $request->status]);
        if ($request->status === 'delivered') {
            $order->update(['delivered_at' => now()]);
            $order->client->increment('total_spent', $order->amount_paid);
        }
        return back()->with('success', 'Statut mis à jour.');
    }

    public function recordPayment(Request $request, Order $order)
    {
        $request->validate([
            'amount'  => 'required|numeric|min:1|max:' . $order->balance,
            'method'  => 'required|in:cash,mobile_money,card,credit',
        ]);
        $this->financeService->recordPayment(
            Order::class, $order->id, $order->client_id,
            $request->amount, $request->method
        );
        return back()->with('success', 'Paiement enregistré.');
    }

    public function invoice(Order $order)
    {
        $order->load([
            'client'        => fn($q) => $q->withTrashed(),
            'cashier'       => fn($q) => $q->withTrashed(),
            'items.product' => fn($q) => $q->withTrashed(),
        ]);
        return view('orders.invoice', compact('order'));
    }

    public function print(Order $order)
    {
        $order->load(['client','items.product']);
        return view('orders.print', compact('order'));
    }

    public function export(Request $request)
    {
        $filters = $request->only(['search', 'status', 'payment_status', 'type']);
        $format  = $request->get('format', 'excel');

        if ($format === 'pdf') {
            $query = Order::with(['client', 'cashier', 'items'])->latest();

            if (!empty($filters['search'])) {
                $s = $filters['search'];
                $query->where(function ($q) use ($s) {
                    $q->where('reference', 'like', "%$s%")
                      ->orWhereHas('client', fn($c) =>
                          $c->where('first_name', 'like', "%$s%")
                            ->orWhere('last_name', 'like', "%$s%")
                            ->orWhere('phone', 'like', "%$s%")
                      );
                });
            }
            if (!empty($filters['status']))
                $query->where('status', $filters['status']);
            if (!empty($filters['payment_status']))
                $query->where('payment_status', $filters['payment_status']);

            $orders = $query->get();

            $pdf = Pdf::loadView('orders.export-pdf', compact('orders'))
                      ->setPaper('a4', 'landscape');

            return $pdf->download('ventes_' . now()->format('Ymd_Hi') . '.pdf');
        }

        // Excel
        $filename = 'ventes_' . now()->format('Ymd_Hi') . '.xlsx';
        return Excel::download(new OrdersExport($filters), $filename);
    }

    public function destroy(Order $order)
    {
        // Seul l'administrateur peut supprimer une commande
        if (!auth()->user()->isAdmin()) {
            return back()->with('error', 'Accès refusé. Seul l\'administrateur peut supprimer une commande.');
        }

        if ($order->status === 'delivered') {
            return back()->with('error', 'Impossible de supprimer une commande livrée.');
        }

        if (in_array($order->payment_status, ['partial', 'paid']) && !auth()->user()->isAdmin()) {
            return back()->with('error', 'Impossible de supprimer une commande avec paiement enregistré.');
        }

        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Commande supprimée.');
    }
}
