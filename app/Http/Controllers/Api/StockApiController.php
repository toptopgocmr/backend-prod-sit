<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Product, StockMovement};
use App\Services\StockService;
use Illuminate\Http\Request;

class StockApiController extends Controller {
    public function __construct(private StockService $stockService) {}
    public function index()    { return response()->json(Product::active()->with('category')->paginate(30)); }
    public function lowStock() { return response()->json($this->stockService->getLowStockReport()); }
    public function movements(){ return response()->json(StockMovement::with(['product','user'])->latest()->paginate(20)); }
    public function addStock(Request $request) {
        $request->validate(['product_id'=>'required|exists:products,id','quantity'=>'required|numeric|min:0.01','reason'=>'required|string']);
        return response()->json($this->stockService->addStock($request->product_id,$request->quantity,$request->reason,$request->reference,$request->unit_cost??0), 201);
    }
    public function adjust(Request $request) {
        $request->validate(['product_id'=>'required|exists:products,id','new_quantity'=>'required|numeric|min:0','reason'=>'required|string']);
        return response()->json($this->stockService->adjust($request->product_id,$request->new_quantity,$request->reason));
    }
}
