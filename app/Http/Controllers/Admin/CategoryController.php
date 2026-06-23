<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name',
            'type'        => 'required|in:tissu,accessoire,pret_a_porter,autre',
            'icon'        => 'nullable|string|max:10',
            'description' => 'nullable|string|max:255',
            'price'       => 'nullable|numeric|min:0',
        ]);

        $validated['slug']      = Str::slug($validated['name']);
        $validated['is_active'] = true;

        Category::create($validated);

        return back()->with('success', 'Catégorie "' . $validated['name'] . '" créée.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name,' . $category->id,
            'type'        => 'required|in:tissu,accessoire,pret_a_porter,autre',
            'icon'        => 'nullable|string|max:10',
            'description' => 'nullable|string|max:255',
            'price'       => 'nullable|numeric|min:0',
            'is_active'   => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $category->update($validated);

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Category $category)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Accès réservé à l\'administrateur.');
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Impossible — cette catégorie contient ' . $category->products()->count() . ' produit(s).');
        }
        $category->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }

    public function toggle(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        return back()->with('success', $category->is_active ? 'Catégorie activée.' : 'Catégorie désactivée.');
    }
}
