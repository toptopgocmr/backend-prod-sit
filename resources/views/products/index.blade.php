@extends('layouts.app')
@section('title', 'Produits')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex-1">
            <p class="text-sm text-gray-500">{{ $products->total() }} produit(s)</p>
        </div>
        <a href="{{ route('products.create') }}"
           class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary-600 transition-colors shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i> Nouveau produit
        </a>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4">
        <div class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Nom, référence..."
                   class="flex-1 min-w-48 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
            <select name="type" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                <option value="">Tous types</option>
                <option value="tissu" {{ request('type')=='tissu' ? 'selected' : '' }}>Tissus</option>
                <option value="pret_a_porter" {{ request('type')=='pret_a_porter' ? 'selected' : '' }}>Prêt-à-porter</option>
                <option value="accessoire" {{ request('type')=='accessoire' ? 'selected' : '' }}>Accessoires</option>
            </select>
            <select name="category" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                <option value="">Toutes catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category')==$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-dark text-white rounded-lg text-sm font-semibold">Filtrer</button>
            @if(request()->hasAny(['search','type','category']))
                <a href="{{ route('products.index') }}" class="px-4 py-2 border border-gray-200 text-gray-500 rounded-lg text-sm">Réinitialiser</a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Produit</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Référence</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Catégorie</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Prix vente</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Stock</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Statut</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($products as $product)
                        <tr class="hover:bg-surface/40 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 overflow-hidden shrink-0">
                                        @if($product->image)
                                            <img src="{{ $product->image_url }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i data-lucide="{{ $product->type === 'tissu' ? 'scissors' : 'package' }}" class="w-4 h-4 text-gray-400"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('products.show', $product) }}" class="text-sm font-semibold text-dark hover:text-primary transition-colors">
                                            {{ $product->name }}
                                        </a>
                                        <p class="text-xs text-gray-400">{{ $product->getTypeLabel() }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-gray-500">{{ $product->reference }}</td>
                            <td class="px-5 py-3.5 text-sm text-gray-600">{{ $product->category->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <p class="text-sm font-bold text-dark">{{ number_format($product->getUnitPrice(), 0, ',', ' ') }}</p>
                                <p class="text-xs text-gray-400">FCFA/{{ $product->getStockUnit() }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <p class="text-base font-bold {{ $product->isLowStock() ? 'text-orange-500' : 'text-dark' }}">
                                    {{ $product->getCurrentStock() }} <span class="text-xs text-gray-400 font-normal">{{ $product->getStockUnit() }}</span>
                                </p>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if(!$product->is_active)
                                    <span class="badge-status bg-gray-50 text-gray-500">Inactif</span>
                                @elseif($product->getCurrentStock() == 0)
                                    <span class="badge-status bg-red-50 text-red-700">Rupture</span>
                                @elseif($product->isLowStock())
                                    <span class="badge-status bg-orange-50 text-orange-700">Stock faible</span>
                                @else
                                    <span class="badge-status bg-green-50 text-green-700">Disponible</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('products.show', $product) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark" title="Voir">
                                        <i data-lucide="eye" style="width:15px;height:15px"></i>
                                    </a>
                                    <a href="{{ route('products.edit', $product) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark" title="Modifier">
                                        <i data-lucide="edit-2" style="width:15px;height:15px"></i>
                                    </a>
                                    <form method="POST" action="{{ route('products.toggle', $product) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark" title="{{ $product->is_active ? 'Désactiver' : 'Activer' }}">
                                            <i data-lucide="{{ $product->is_active ? 'eye-off' : 'eye' }}" style="width:15px;height:15px"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-gray-400">
                                <i data-lucide="package" class="w-10 h-10 mx-auto mb-3 text-gray-200"></i>
                                <p class="font-medium">Aucun produit trouvé</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">{{ $products->links() }}</div>
        @endif
    </div>

</div>
@endsection
