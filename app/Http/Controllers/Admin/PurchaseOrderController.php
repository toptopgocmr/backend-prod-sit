<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{PurchaseOrder, PurchaseOrderItem, Product};
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller {
    public function __construct(private StockService $stockService) {}
    public function index() { return view('stock.purchase-orders', ['orders'=>PurchaseOrder::with('items')->latest()->paginate(15)]); }
    public function create() { return view('stock.purchase-order-create', ['products'=>Product::active()->orderBy('name')->get()]); }
    public function store(Request $request) {
        $validated = $request->validate(['supplier_name'=>'required|string','items'=>'required|array|min:1','items.*.product_id'=>'required|exists:products,id','items.*.quantity_ordered'=>'required|numeric|min:0.01','items.*.unit_cost'=>'required|numeric|min:0']);
        DB::transaction(function() use ($validated, $request) {
            $total = collect($validated['items'])->sum(fn($i) => $i['unit_cost'] * $i['quantity_ordered']);
            $po = PurchaseOrder::create(['reference'=>'BON-'.date('Ymd').'-'.strtoupper(Str::random(5)),'user_id'=>auth()->id(),'supplier_name'=>$validated['supplier_name'],'total_amount'=>$total,'status'=>'ordered']);
            foreach ($validated['items'] as $item) {
                $p = Product::find($item['product_id']);
                PurchaseOrderItem::create(['purchase_order_id'=>$po->id,'product_id'=>$p->id,'product_name'=>$p->name,'quantity_ordered'=>$item['quantity_ordered'],'quantity_received'=>0,'unit_cost'=>$item['unit_cost'],'total'=>$item['unit_cost']*$item['quantity_ordered']]);
            }
        });
        return redirect()->route('purchase-orders.index')->with('success','Bon de commande créé.');
    }
    public function show(PurchaseOrder $purchaseOrder) { return view('stock.purchase-order-show', compact('purchaseOrder')); }
    public function edit(PurchaseOrder $purchaseOrder) { return view('stock.purchase-order-edit', compact('purchaseOrder')); }
    public function update(Request $request, PurchaseOrder $purchaseOrder) { return back(); }
    public function destroy(PurchaseOrder $purchaseOrder) { $purchaseOrder->delete(); return redirect()->route('purchase-orders.index'); }
    public function receive(Request $request, PurchaseOrder $purchaseOrder) {
        DB::transaction(function() use ($request, $purchaseOrder) {
            foreach ($request->items ?? [] as $itemId => $qty) {
                if ($qty <= 0) continue;
                $item = PurchaseOrderItem::find($itemId);
                if (!$item) continue;
                $item->increment('quantity_received', $qty);
                $this->stockService->addStock($item->product_id, $qty, "Réception {$purchaseOrder->reference}", $purchaseOrder->reference, $item->unit_cost);
            }
            $purchaseOrder->update(['status'=>'received','received_date'=>now()->toDateString()]);
        });
        return back()->with('success','Réception enregistrée.');
    }
}
