<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{PurchaseOrder, PurchaseOrderItem, Product, Expense, ExpenseCategory};
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index()
    {
        return view('stock.purchase-orders', [
            'orders' => PurchaseOrder::with('items')->latest()->paginate(15)
        ]);
    }

    public function create()
    {
        return view('stock.purchase-order-create', [
            'products' => Product::active()->orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name'              => 'required|string',
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => 'required|exists:products,id',
            'items.*.quantity_ordered'   => 'required|numeric|min:0.01',
            'items.*.unit_cost'          => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $total = collect($validated['items'])->sum(fn($i) => $i['unit_cost'] * $i['quantity_ordered']);
            $po = PurchaseOrder::create([
                'reference'     => 'BON-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                'user_id'       => auth()->id(),
                'supplier_name' => $validated['supplier_name'],
                'total_amount'  => $total,
                'status'        => 'ordered',
            ]);
            foreach ($validated['items'] as $item) {
                $p = Product::find($item['product_id']);
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'        => $p->id,
                    'product_name'      => $p->name,
                    'quantity_ordered'  => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_cost'         => $item['unit_cost'],
                    'total'             => $item['unit_cost'] * $item['quantity_ordered'],
                ]);
            }
        });

        return redirect()->route('purchase-orders.index')->with('success', 'Bon de commande créé.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        return view('stock.purchase-order-show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        return view('stock.purchase-order-edit', compact('purchaseOrder'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        return back();
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Accès réservé à l\'administrateur.');
        $purchaseOrder->delete();
        return redirect()->route('purchase-orders.index')->with('success', 'Bon de commande supprimé.');
    }

    /**
     * Annuler un bon de commande (sans suppression définitive).
     * Disponible pour admin et stock_manager.
     */
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'received') {
            return back()->with('error', 'Impossible d\'annuler un bon de commande déjà réceptionné.');
        }
        $purchaseOrder->update(['status' => 'cancelled']);
        return back()->with('success', 'Bon de commande annulé.');
    }

    /**
     * Réceptionner les articles ET enregistrer automatiquement une dépense finance.
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        DB::transaction(function () use ($request, $purchaseOrder) {

            $totalReceived = 0;

            foreach ($request->items ?? [] as $itemId => $qty) {
                if ($qty <= 0) continue;
                $item = PurchaseOrderItem::find($itemId);
                if (!$item) continue;

                $item->increment('quantity_received', $qty);
                $lineTotal = $qty * $item->unit_cost;
                $totalReceived += $lineTotal;

                $this->stockService->addStock(
                    $item->product_id,
                    $qty,
                    "Réception {$purchaseOrder->reference}",
                    $purchaseOrder->reference,
                    $item->unit_cost
                );
            }

            // ── Enregistrement automatique en dépense finance ──
            if ($totalReceived > 0) {
                // Chercher ou créer la catégorie "Achats stock"
                $category = ExpenseCategory::firstOrCreate(
                    ['type' => 'achat'],
                    [
                        'name'  => 'Achats stock',
                        'color' => '#F59E0B',
                        'icon'  => 'package',
                    ]
                );

                $paymentMethod = $request->input('payment_method', 'cash');

                $expense = Expense::create([
                    'expense_category_id' => $category->id,
                    'user_id'             => auth()->id(),
                    'label'               => "Achat stock — {$purchaseOrder->reference} ({$purchaseOrder->supplier_name})",
                    'amount'              => $totalReceived,
                    'payment_method'      => $paymentMethod,
                    'expense_date'        => now()->toDateString(),
                    'reference'           => $purchaseOrder->reference,
                    'notes'               => "Réception automatique du bon de commande {$purchaseOrder->reference}",
                    'is_validated'        => true,
                    'validated_by'        => auth()->id(),
                ]);

                // Lier la dépense au bon de commande
                $purchaseOrder->update([
                    'status'         => 'received',
                    'received_da