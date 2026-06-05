@extends('layouts.app')
@section('title', 'Gestion du Stock')

@section('content')
<div class="space-y-5">

    {{-- Stats rapides --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-2xl font-display font-bold text-dark">{{ $stats['total_products'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Produits actifs</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-2xl font-display font-bold text-orange-500">{{ $stats['low_stock'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Stock faible</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-2xl font-display font-bold text-red-500">{{ $stats['out_of_stock'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Rupture de stock</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-lg font-display font-bold text-dark">{{ number_format($stats['total_value'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400 mt-1">Valeur stock (FCFA)</p>
        </div>
    </div>

    {{-- Actions rapides --}}
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary-600 transition-colors">
            <i data-lucide="plus" class="w-4 h-4"></i> Nouveau produit
        </a>
        <button x-data @click="$dispatch('open-add-stock')"
                class="inline-flex items-center gap-2 bg-white border border-gray-200 text-dark px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
            <i data-lucide="arrow-up-circle" class="w-4 h-4 text-green-600"></i> Entrée stock
        </button>
        <a href="{{ route('stock.movements') }}" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-dark px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
            <i data-lucide="activity" class="w-4 h-4"></i> Mouvements
        </a>
        <a href="{{ route('stock.low') }}" class="inline-flex items-center gap-2 bg-orange-50 border border-orange-100 text-orange-700 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-orange-100 transition-colors">
            <i data-lucide="alert-triangle" class="w-4 h-4"></i> Stock faible
        </a>

        {{-- Dropdown Export --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.outside="open = false"
                    class="inline-flex items-center gap-2 bg-white border border-gray-200 text-dark px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                <i data-lucide="download" class="w-4 h-4 text-blue-500"></i>
                Exporter
                <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute left-0 mt-1.5 w-56 bg-white border border-gray-100 rounded-xl shadow-lg z-20 overflow-hidden"
                 style="display:none;">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-50">
                    Stock
                </div>
                <a href="{{ route('stock.export.excel') }}"
                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-green-600"></i>
                    Stock complet — Excel
                </a>
                <a href="{{ route('stock.export.pdf') }}"
                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="file-text" class="w-4 h-4 text-red-500"></i>
                    Stock complet — PDF
                </a>
                <div class="border-t border-gray-50"></div>
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-50">
                    Mouvements
                </div>
                <a href="{{ route('stock.export.movements-excel') }}"
                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-green-600"></i>
                    Mouvements — Excel
                </a>
            </div>
        </div>
    </div>

    {{-- Tableau produits --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Produit</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Référence</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Catégorie</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Prix unitaire</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Stock</th>
                        <th class="px-5 py-3.5 text-center font-semibold">État</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($products as $product)
                        <tr class="hover:bg-surface/40 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center shrink-0 overflow-hidden">
                                        @if($product->image)
                                            <img src="{{ $product->image_url }}" class="w-full h-full object-cover">
                                        @else
                                            <i data-lucide="{{ $product->type === 'tissu' ? 'scissors' : 'package' }}" class="w-4 h-4 text-gray-400"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-dark">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $product->getTypeLabel() }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-gray-500">{{ $product->reference }}</td>
                            <td class="px-5 py-3.5 text-sm text-gray-600">{{ $product->category->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-right text-sm font-semibold text-dark">
                                {{ number_format($product->getUnitPrice(), 0, ',', ' ') }}
                                <span class="text-xs text-gray-400 font-normal">FCFA/{{ $product->getStockUnit() }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-base font-bold {{ $product->isLowStock() ? 'text-red-600' : 'text-dark' }}">
                                        {{ $product->getCurrentStock() }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $product->getStockUnit() }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($product->getCurrentStock() == 0)
                                    <span class="badge-status bg-red-50 text-red-700">Rupture</span>
                                @elseif($product->isLowStock())
                                    <span class="badge-status bg-orange-50 text-orange-700">Stock faible</span>
                                @else
                                    <span class="badge-status bg-green-50 text-green-700">OK</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="$dispatch('open-add-stock', { id: {{ $product->id }}, name: '{{ addslashes($product->name) }}' })"
                                            class="p-1.5 rounded-lg hover:bg-green-50 text-gray-400 hover:text-green-600 transition-colors" title="Ajouter stock">
                                        <i data-lucide="plus-circle" style="width:15px;height:15px"></i>
                                    </button>
                                    <a href="{{ route('products.edit', $product) }}"
                                       class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark transition-colors" title="Modifier">
                                        <i data-lucide="edit-2" style="width:15px;height:15px"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-gray-400">
                                <i data-lucide="package" class="w-10 h-10 mx-auto mb-3 text-gray-200"></i>
                                <p>Aucun produit trouvé</p>
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

{{-- Modal Entrée Stock --}}
<div x-data="{ open: false, productId: null, productName: '' }"
     @open-add-stock.window="open = true; productId = $event.detail?.id || null; productName = $event.detail?.name || ''"
     x-show="open" x-cloak
     class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div @click.outside="open = false" class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
        <h3 class="font-display font-bold text-dark text-lg mb-4">Entrée en stock</h3>
        <form method="POST" action="{{ route('stock.add') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="product_id" :value="productId">
            <div>
                <label class="block text-sm font-semibold text-dark mb-1">Produit</label>
                <select name="product_id" x-show="!productId" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
                <p x-show="productId" class="text-sm text-dark font-medium bg-gray-50 px-3 py-2 rounded-xl" x-text="productName"></p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-dark mb-1">Quantité reçue</label>
                <input type="number" name="quantity" min="0.01" step="0.01" required
                       class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div>
                <label class="block text-sm font-semibold text-dark mb-1">Coût unitaire (FCFA)</label>
                <input type="number" name="unit_cost" min="0" step="1"
                       class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div>
                <label class="block text-sm font-semibold text-dark mb-1">Motif / N° bon livraison</label>
                <input type="text" name="reason" required placeholder="Ex: Réception fournisseur..."
                       class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="open = false"
                        class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-50">Annuler</button>
                <button type="submit"
                        class="flex-1 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary-600">Confirmer</button>
            </div>
        </form>
    </div>
</div>
@endsection
