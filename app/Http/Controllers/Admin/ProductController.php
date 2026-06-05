<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Product, Category};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->latest();

        if ($request->filled('type'))     $query->where('type', $request->type);
        if ($request->filled('category')) $query->where('category_id', $request->category);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name','like',"%$s%")->orWhere('reference','like',"%$s%"));
        }
        if ($request->filled('stock')) {
            if ($request->stock === 'low')  $query->whereRaw('stock_quantity <= alert_threshold');
            if ($request->stock === 'zero') $query->where('stock_quantity', 0);
        }

        $products   = $query->paginate(20)->withQueryString();
        $categories = Category::all();
        return view('products.index', compact('products','categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'     => 'required|exists:categories,id',
            'name'            => 'required|string|max:200',
            'description'     => 'nullable|string',
            'type'            => 'required|in:tissu,pret_a_porter,accessoire',
            'gender'          => 'nullable|in:homme,femme,enfant_fille,enfant_garcon,mixte',
            'price_per_meter' => 'nullable|numeric|min:0',
            'available_meters'=> 'nullable|numeric|min:0',
            'min_meters'      => 'nullable|numeric|min:0.5',
            'price'           => 'nullable|numeric|min:0',
            'size'            => 'nullable|string|max:20',
            'color'           => 'nullable|string|max:50',
            'stock_quantity'  => 'nullable|integer|min:0',
            'alert_threshold' => 'required|integer|min:0',
            'cost_price'      => 'nullable|numeric|min:0',
            'is_active'       => 'boolean',
            'is_featured'     => 'boolean',
            'image'           => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);
        return redirect()->route('products.index')->with('success', 'Produit créé avec succès.');
    }

    public function show(Product $product)
    {
        $product->load(['category','stockMovements' => fn($q) => $q->latest()->limit(15)]);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product','categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id'     => 'required|exists:categories,id',
            'name'            => 'required|string|max:200',
            'description'     => 'nullable|string',
            'gender'          => 'nullable|in:homme,femme,enfant_fille,enfant_garcon,mixte',
            'price_per_meter' => 'nullable|numeric|min:0',
            'min_meters'      => 'nullable|numeric|min:0.5',
            'price'           => 'nullable|numeric|min:0',
            'size'            => 'nullable|string|max:20',
            'color'           => 'nullable|string|max:50',
            'alert_threshold' => 'required|integer|min:0',
            'cost_price'      => 'nullable|numeric|min:0',
            'is_active'       => 'boolean',
            'is_featured'     => 'boolean',
            'image'           => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);
        return redirect()->route('products.index')->with('success', 'Produit mis à jour.');
    }

    public function toggle(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        return back()->with('success', 'Produit ' . ($product->is_active ? 'activé' : 'désactivé') . '.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produit supprimé.');
    }

    public function export()
    {
        // TODO: implémenter export Excel via Maatwebsite
        return back()->with('error', 'Export en cours d\'implémentation.');
    }
}
