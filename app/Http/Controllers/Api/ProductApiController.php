<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductApiController extends Controller {
    public function index(Request $request) {
        return response()->json(Product::active()->when($request->type,fn($q,$t)=>$q->where('type',$t))->with('category')->paginate(30));
    }
    public function show(Product $product) { return response()->json($product->load('category')); }
    public function store(Request $request) {
        return response()->json(Product::create($request->validate(['category_id'=>'required|exists:categories,id','name'=>'required|string','type'=>'required|in:tissu,pret_a_porter,accessoire','alert_threshold'=>'required|integer|min:0'])), 201);
    }
    public function update(Request $request, Product $product) { $product->update($request->all()); return response()->json($product); }

    public function destroy(Product $product) {
        // Soft-delete: check if it has stock movements or order items first
        if ($product->stockMovements()->exists() || $product->orderItems()->exists()) {
            // Don't delete — just deactivate
            $product->update(['is_active' => false]);
            return response()->json(['message' => 'Produit désactivé (historique existant).']);
        }
        $product->delete();
        return response()->json(['message' => 'Produit supprimé.']);
    }
}
