@extends('layouts.app')
@section('title', 'Nouvelle vente')

@section('breadcrumb')
    <a href="{{ route('orders.index') }}" class="hover:text-gray-600 transition-colors">Ventes</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Nouvelle vente</span>
@endsection

@section('content')

@php
$clientsJson = $clients->map(fn($c) => [
    'id'    => $c->id,
    'name'  => $c->full_name,
    'phone' => $c->phone,
])->values();

$productsJson = $products->map(fn($p) => [
    'id'       => $p->id,
    'name'     => $p->name,
    'reference'=> $p->reference,
    'category' => $p->category?->name ?? '',
    'type'     => $p->type ?? 'pret_a_porter',
    'price'    => $p->getUnitPrice(),
    'stock'    => $p->getCurrentStock(),
    'unit'     => $p->getStockUnit(),
    'image'    => $p->image_url,
])->values();
@endphp

<script>
const CLIENTS  = {!! json_encode($clientsJson) !!};
const PRODUCTS = {!! json_encode($productsJson) !!};
const CURRENCY = '{{ env("CURRENCY", "FCFA") }}';
</script>

<form method="POST" action="{{ route('orders.store') }}"
      x-data="saleForm()"
      x-on:submit.prevent="submitForm"
      id="saleForm">
    @csrf

    @if($errors->any())
    <div class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3.5 rounded-xl text-sm">
        <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 shrink-0 mt-0.5"></i>
        <ul class="space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ══════════ COLONNE GAUCHE ══════════ --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- En-tête --}}
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-2xl bg-orange-50 flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-5 h-5 text-primary"></i>
                </div>
                <div>
                    <h2 class="text-lg font-display font-bold text-dark">Nouvelle vente</h2>
                    <p class="text-xs text-gray-400">Sélectionnez le client et les articles à vendre</p>
                </div>
            </div>

            {{-- ── 1. Client ──────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-primary"></i>
                    Client
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Client <span class="text-red-400">*</span></label>
                        <div x-data="searchSelect({
                                items: CLIENTS,
                                labelKey: 'name',
                                subKey: 'phone',
                                placeholder: 'Rechercher un client...',
                                onSelect: (id) => { clientId = id; }
                             })" class="relative">
                            <input type="hidden" name="client_id" x-model="selectedId">
                            <button type="button" x-on:click="open = !open"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm text-left
                                           focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                <span :class="selectedId ? 'text-dark' : 'text-gray-400'"
                                      x-text="selectedId ? (items.find(i=>i.id==selectedId)?.name + ' — ' + items.find(i=>i.id==selectedId)?.phone) : placeholder"></span>
                                <i data-lucide="chevrons-up-down" style="width:14px;height:14px" class="text-gray-400 shrink-0 ml-2"></i>
                            </button>
                            <div x-show="open" x-on:click.outside="open=false" x-transition
                                 class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                                <div class="p-2 border-b border-gray-100">
                                    <input type="text" x-model="search" x-on:input="filterItems"
                                           placeholder="Taper pour filtrer..."
                                           class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                                </div>
                                <ul class="max-h-48 overflow-y-auto">
                                    <template x-for="item in filtered" :key="item.id">
                                        <li x-on:click="select(item)"
                                            class="flex items-center gap-3 px-3 py-2.5 hover:bg-orange-50 cursor-pointer transition-colors"
                                            :class="selectedId == item.id ? 'bg-orange-50' : ''">
                                            <div class="w-7 h-7 rounded-full bg-orange-100 flex items-center justify-center text-primary font-bold text-xs shrink-0"
                                                 x-text="item.name.charAt(0).toUpperCase()"></div>
                                            <div>
                                                <p class="text-sm font-semibold text-dark" x-text="item.name"></p>
                                                <p class="text-xs text-gray-400" x-text="item.phone"></p>
                                            </div>
                                            <i x-show="selectedId == item.id" data-lucide="check" style="width:14px;height:14px" class="text-primary ml-auto shrink-0"></i>
                                        </li>
                                    </template>
                                    <li x-show="filtered.length === 0" class="px-3 py-4 text-center text-gray-400 text-sm">Aucun résultat</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Notes commande</label>
                        <input type="text" name="notes" value="{{ old('notes') }}"
                               placeholder="Référence, remarque client..."
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark
                                      focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                </div>
            </div>

            {{-- ── 2. Articles ────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="package" class="w-4 h-4 text-primary"></i>
                        Articles
                        <span class="text-xs bg-orange-100 text-primary px-2 py-0.5 rounded-full font-bold"
                              x-text="items.length"></span>
                    </h3>
                    <div class="relative flex-1 max-w-sm">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" style="width:13px;height:13px"></i>
                        <input type="text" x-model="catalogSearch"
                               placeholder="Filtrer par nom, référence, catégorie..."
                               class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-gray-200 text-xs text-dark
                                      focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                </div>

                {{-- Liste catalogue scrollable --}}
                <div class="border-b border-gray-50">
                    <div class="overflow-y-auto" style="max-height: 320px;">
                        <table class="w-full text-sm">
                            <thead class="sticky top-0 bg-gray-50/95 backdrop-blur-sm z-10">
                                <tr class="text-xs text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                    <th class="px-4 py-2.5 text-left font-semibold">Produit</th>
                                    <th class="px-4 py-2.5 text-left font-semibold hidden sm:table-cell">Catégorie</th>
                                    <th class="px-4 py-2.5 text-right font-semibold">Prix</th>
                                    <th class="px-4 py-2.5 text-center font-semibold">Stock</th>
                                    <th class="px-4 py-2.5 text-center font-semibold w-16"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-for="p in filteredCatalog" :key="p.id">
                                    <tr :class="isInCart(p.id) ? 'bg-orange-50' : (p.stock > 0 ? 'hover:bg-gray-50' : 'opacity-50')"
                                        class="transition-colors">
                                        {{-- Image + Nom --}}
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 shrink-0 border border-gray-200">
                                                    <img x-show="p.image" :src="p.image" class="w-full h-full object-cover">
                                                    <div x-show="!p.image" class="w-full h-full flex items-center justify-center">
                                                        <i data-lucide="shirt" style="width:16px;height:16px" class="text-gray-300"></i>
                                                    </div>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-semibold text-dark truncate" x-text="p.name"></p>
                                                    <p class="text-xs text-gray-400" x-text="p.reference"></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 hidden sm:table-cell">
                                            <span class="text-xs text-gray-500" x-text="p.category || '—'"></span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="text-sm font-bold text-dark" x-text="fmtP(p.price)"></span>
                                            <p class="text-xs text-gray-400" x-text="'/ ' + p.unit"></p>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-xs font-semibold" :class="p.stock > 0 ? 'text-green-600' : 'text-red-400'"
                                                  x-text="p.stock > 0 ? p.stock + ' ' + p.unit : 'Rupture'"></span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button type="button"
                                                    x-on:click="p.stock > 0 && addItem(p)"
                                                    :disabled="p.stock <= 0"
                                                    :class="isInCart(p.id)
                                                        ? 'bg-primary text-white'
                                                        : (p.stock > 0 ? 'bg-orange-50 text-primary hover:bg-primary hover:text-white' : 'bg-gray-100 text-gray-300 cursor-not-allowed')"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors mx-auto">
                                                <i x-show="isInCart(p.id)" data-lucide="check" style="width:14px;height:14px"></i>
                                                <i x-show="!isInCart(p.id)" data-lucide="plus" style="width:14px;height:14px"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="filteredCatalog.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">Aucun produit trouvé</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Articles sélectionnés --}}
                <div x-show="items.length === 0" class="px-5 py-6 text-center text-gray-400 text-sm">
                    <i data-lucide="shopping-cart" class="w-7 h-7 mx-auto mb-2 text-gray-200"></i>
                    Aucun article sélectionné
                </div>

                <div class="divide-y divide-gray-50">
                    <template x-for="(item, index) in items" :key="item._key">
                        <div class="px-5 py-4">
                            {{-- En-tête avec image --}}
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 shrink-0 border border-gray-200">
                                    <img x-show="item.image" :src="item.image" class="w-full h-full object-cover">
                                    <div x-show="!item.image" class="w-full h-full flex items-center justify-center bg-orange-50">
                                        <i data-lucide="shirt" style="width:18px;height:18px" class="text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-dark truncate" x-text="item.name"></p>
                                    <p class="text-xs text-gray-400" x-text="item.reference"></p>
                                    <p class="text-sm font-semibold text-primary mt-0.5" x-text="fmtP(item.price) + ' / ' + item.unit"></p>
                                </div>
                                <button type="button" x-on:click="removeItem(index)"
                                        class="w-7 h-7 rounded-lg hover:bg-red-50 text-gray-300 hover:text-red-500 flex items-center justify-center transition-colors shrink-0">
                                    <i data-lucide="x" style="width:13px;height:13px"></i>
                                </button>
                            </div>

                            {{-- Taille (hors tissu) + Couleur --}}
                            <div class="mt-3 grid grid-cols-2 gap-2" style="margin-left:60px">
                                <div x-show="item.type !== 'tissu'">
                                    <label class="block text-xs text-gray-400 mb-1">Taille</label>
                                    <select :name="`items[${index}][size]`" x-model="item.size"
                                            class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm text-dark
                                                   focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                        <option value="">— Taille —</option>
                                        <option>XS</option><option>S</option><option>M</option>
                                        <option>L</option><option>XL</option><option>XXL</option>
                                        <option>3XL</option><option>4XL</option>
                                        <option>36</option><option>38</option><option>40</option>
                                        <option>42</option><option>44</option><option>46</option>
                                        <option>48</option><option>50</option><option>52</option><option>54</option>
                                        <option value="unique">Unique</option>
                                    </select>
                                </div>
                                {{-- Pour tissu : champ taille vide mais caché --}}
                                <input x-show="item.type === 'tissu'" type="hidden" :name="`items[${index}][size]`" value="">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Couleur</label>
                                    <select :name="`items[${index}][color]`" x-model="item.color"
                                            class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm text-dark
                                                   focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                        <option value="">— Couleur —</option>
                                        <option>Blanc</option><option>Noir</option><option>Gris</option>
                                        <option>Beige</option><option>Crème</option>
                                        <option>Bleu marine</option><option>Bleu ciel</option><option>Bleu roi</option>
                                        <option>Rouge</option><option>Bordeaux</option>
                                        <option>Vert</option><option>Vert kaki</option>
                                        <option>Jaune</option><option>Orange</option>
                                        <option>Violet</option><option>Rose</option>
                                        <option>Marron</option><option>Caramel</option>
                                        <option>Or</option><option>Argenté</option>
                                        <option>Multicolore</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Quantité + Remise + Total --}}
                            <div class="mt-2 grid grid-cols-3 gap-2 items-end" style="margin-left:60px">
                                {{-- Quantité --}}
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1"
                                           x-text="item.type === 'tissu' ? 'Mètres' : 'Quantité'"></label>
                                    <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                                        <button type="button"
                                                x-on:click="item.qty = Math.max(item.type==='tissu' ? 0.5 : 1, parseFloat((item.qty - (item.type==='tissu' ? 0.5 : 1)).toFixed(1))); calcTotals()"
                                                class="w-8 h-8 flex items-center justify-center text-gray-400 hover:bg-gray-50 hover:text-dark transition-colors font-bold">−</button>
                                        <input type="number" :name="`items[${index}][quantity]`"
                                               x-model.number="item.qty" x-on:input="calcTotals"
                                               :max="item.stock"
                                               :min="item.type === 'tissu' ? 0.5 : 1"
                                               :step="item.type === 'tissu' ? 0.5 : 1"
                                               class="w-14 text-center text-sm font-bold text-dark border-0 focus:outline-none focus:ring-0 py-1.5">
                                        <button type="button"
                                                x-on:click="item.qty = Math.min(item.stock, parseFloat((item.qty + (item.type==='tissu' ? 0.5 : 1)).toFixed(1))); calcTotals()"
                                                class="w-8 h-8 flex items-center justify-center text-gray-400 hover:bg-gray-50 hover:text-dark transition-colors font-bold">+</button>
                                    </div>
                                    <p class="text-xs text-gray-300 mt-0.5" x-text="'max ' + item.stock + ' ' + item.unit"></p>
                                </div>

                                {{-- Remise % --}}
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Remise %</label>
                                    <div class="flex items-center gap-1">
                                        <input type="number" :name="`items[${index}][discount]`"
                                               x-model.number="item.discount" x-on:input="calcTotals"
                                               min="0" max="100" step="1" placeholder="0"
                                               class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm text-dark text-right
                                                      focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                        <span class="text-xs text-gray-400">%</span>
                                    </div>
                                </div>

                                {{-- Sous-total --}}
                                <div class="text-right">
                                    <p class="text-xs text-gray-400 mb-1">Sous-total</p>
                                    <p class="text-sm font-bold text-dark" x-text="fmt(lineTotal(item))"></p>
                                    <p x-show="item.discount > 0" class="text-xs text-green-600"
                                       x-text="'−' + fmt(item.price * item.qty * item.discount / 100)"></p>
                                </div>
                            </div>

                            {{-- Hidden --}}
                            <input type="hidden" :name="`items[${index}][product_id]`" :value="item.id">
                            <input type="hidden" :name="`items[${index}][unit_price]`" :value="item.price">
                        </div>
                    </template>
                </div>

                {{-- Pied --}}
                <div x-show="items.length > 0" class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <span class="text-xs text-gray-500" x-text="items.length + ' article(s) · ' + totalQty() + ' unité(s)'"></span>
                    <span class="text-sm font-bold text-dark" x-text="fmt(subtotal)"></span>
                </div>
            </div>

            {{-- ── 3. Livraison (optionnel) ──────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-4 h-4 text-primary"></i>
                    Livraison
                    <span class="text-xs text-gray-400 font-normal">(optionnel)</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Adresse de livraison</label>
                        <input type="text" name="delivery_address" value="{{ old('delivery_address') }}"
                               placeholder="Quartier, rue, repère..."
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark
                                      focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Date de livraison</label>
                        <input type="date" name="delivery_date" value="{{ old('delivery_date') }}"
                               min="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark
                                      focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                </div>
            </div>

        </div>

        {{-- ══════════ COLONNE DROITE ══════════ --}}
        <div class="space-y-5">

            {{-- Récapitulatif --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden sticky top-6">
                <div class="px-5 py-4 border-b border-gray-50">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-primary"></i>
                        Récapitulatif
                    </h3>
                </div>
                <div class="p-5 space-y-4">

                    {{-- Lignes --}}
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Sous-total</span>
                            <span class="font-semibold text-dark" x-text="fmt(subtotal)"></span>
                        </div>
                        <input type="hidden" name="discount" value="0">
                        <input type="hidden" name="type" :value="orderType">
                        <div class="border-t border-gray-100 pt-2 flex justify-between">
                            <span class="font-display font-bold text-dark">TOTAL</span>
                            <span class="text-xl font-display font-bold text-primary" x-text="fmt(total)"></span>
                        </div>
                    </div>

                    {{-- Paiement --}}
                    <div class="space-y-3 pt-2 border-t border-gray-50">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Paiement</h4>

                        <div>
                            <label class="block text-xs text-gray-500 mb-1.5">Mode de paiement</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach(['cash' => ['Espèces','banknote'], 'mobile_money' => ['Mobile','smartphone'], 'card' => ['Carte','credit-card'], 'credit' => ['Crédit','clock']] as $val => $info)
                                    <label class="flex items-center gap-2 px-3 py-2 rounded-xl border cursor-pointer text-xs font-semibold transition-all"
                                           :class="paymentMethod === '{{ $val }}' ? 'border-primary bg-orange-50 text-primary' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                                        <input type="radio" name="payment_method" value="{{ $val }}" x-model="paymentMethod" class="sr-only">
                                        <i data-lucide="{{ $info[1] }}" style="width:13px;height:13px"></i>
                                        {{ $info[0] }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-500 mb-1.5">Montant reçu</label>
                            <div class="relative">
                                <input type="number" name="amount_paid" x-model.number="amountPaid"
                                       x-on:input="calcChange"
                                       min="0" step="100" placeholder="0"
                                       class="w-full px-3 py-2.5 pr-16 rounded-xl border border-gray-200 text-sm font-bold text-dark text-right
                                              focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400 pointer-events-none"
                                      x-text="CURRENCY"></span>
                            </div>
                            <button type="button" x-on:click="amountPaid = total; calcChange()"
                                    class="mt-2 w-full text-xs text-primary font-semibold py-1.5 rounded-lg border border-orange-200 bg-orange-50 hover:bg-orange-100 transition-colors">
                                Paiement intégral
                            </button>
                        </div>

                        {{-- Monnaie à rendre --}}
                        <div x-show="amountPaid > total && total > 0" x-transition
                             class="flex justify-between px-3 py-2.5 rounded-xl bg-green-50 border border-green-100">
                            <span class="text-xs font-semibold text-green-700">Monnaie à rendre</span>
                            <span class="text-sm font-bold text-green-700" x-text="fmt(amountPaid - total)"></span>
                        </div>

                        {{-- Reste à payer --}}
                        <div x-show="amountPaid < total && amountPaid > 0" x-transition
                             class="flex justify-between px-3 py-2.5 rounded-xl bg-orange-50 border border-orange-100">
                            <span class="text-xs font-semibold text-orange-700">Reste à payer</span>
                            <span class="text-sm font-bold text-orange-700" x-text="fmt(total - amountPaid)"></span>
                        </div>

                        {{-- Statut paiement --}}
                        <div class="flex justify-center">
                            <span class="badge-status text-xs px-3 py-1"
                                  :class="{
                                      'bg-green-50 text-green-700': paymentStatus === 'paid',
                                      'bg-yellow-50 text-yellow-700': paymentStatus === 'partial',
                                      'bg-red-50 text-red-700': paymentStatus === 'unpaid'
                                  }">
                                <span x-show="paymentStatus === 'paid'">✓ Soldé</span>
                                <span x-show="paymentStatus === 'partial'">◑ Acompte partiel</span>
                                <span x-show="paymentStatus === 'unpaid'">○ Non payé</span>
                            </span>
                        </div>
                    </div>

                    {{-- Boutons --}}
                    <div class="space-y-2 pt-2 border-t border-gray-50">
                        <button type="submit" :disabled="!isValid"
                                class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-primary text-white
                                       text-sm font-bold hover:bg-primary-600 active:scale-95 transition-all shadow-sm shadow-primary/20
                                       disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="check-circle" style="width:16px;height:16px"></i>
                            Enregistrer la vente
                        </button>
                        <a href="{{ route('orders.index') }}"
                           class="w-full flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-gray-200
                                  text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                            Annuler
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</form>

<script>

/* ── searchSelect réutilisable ──────────────────────────── */
function searchSelect({ items, labelKey, subKey, placeholder, onSelect }) {
    return {
        items, labelKey, subKey, placeholder, onSelect,
        open: false, search: '', selectedId: '', filtered: [],
        init() {
            this.filtered = this.items;
            this.$watch('open', v => {
                if (v) {
                    this.search = '';
                    this.filtered = this.items;
                    this.$nextTick(() => {
                        this.$el.querySelector('input[type=text]')?.focus();
                        lucide.createIcons();
                    });
                }
            });
        },
        filterItems() {
            const q = this.search.toLowerCase();
            this.filtered = this.items.filter(i =>
                (i[this.labelKey] || '').toLowerCase().includes(q) ||
                (i[this.subKey]   || '').toLowerCase().includes(q)
            );
        },
        select(item) {
            this.selectedId = item.id;
            this.open = false;
            if (this.onSelect) this.onSelect(item.id);
        },
    };
}

/* ── Formulaire principal ───────────────────────────────── */
function saleForm() {
    return {
        clientId:       '',
        items:          [],
        _counter:       0,
        globalDiscount: 0,
        amountPaid:     0,
        paymentMethod:  'cash',
        catalogSearch:  '',

        subtotal:       0,
        total:          0,
        balance:        0,
        paymentStatus:  'unpaid',

        get filteredCatalog() {
            const q = this.catalogSearch.toLowerCase();
            if (!q) return PRODUCTS;
            return PRODUCTS.filter(p =>
                p.name.toLowerCase().includes(q) ||
                p.reference.toLowerCase().includes(q) ||
                (p.category || '').toLowerCase().includes(q)
            );
        },

        get isValid() {
            return this.clientId && this.items.length > 0;
        },

        get orderType() {
            const types = [...new Set(this.items.map(i => i.type || 'pret_a_porter'))];
            if (types.length === 0) return 'pret_a_porter';
            if (types.every(t => t === 'tissu')) return 'tissu';
            if (types.every(t => t !== 'tissu')) return 'pret_a_porter';
            return 'mixte';
        },

        init() {},

        isInCart(id) {
            return this.items.some(i => i.id === id);
        },

        addItem(p) {
            const existing = this.items.find(i => i.id === p.id);
            if (existing) {
                existing.qty = Math.min(existing.stock, existing.qty + 1);
            } else {
                this.items.push({
                    _key:      ++this._counter,
                    id:        p.id,
                    name:      p.name,
                    reference: p.reference,
                    type:      p.type || 'pret_a_porter',
                    price:     p.price,
                    stock:     p.stock,
                    unit:      p.unit || 'pcs',
                    image:     p.image || null,
                    qty:       1,
                    discount:  0,
                    size:      '',
                    color:     '',
                });
                this.$nextTick(() => lucide.createIcons());
            }
            this.calcTotals();
        },

        removeItem(index) {
            this.items.splice(index, 1);
            this.calcTotals();
        },

        lineTotal(item) {
            const base = item.price * item.qty;
            return base - (base * (item.discount || 0) / 100);
        },

        totalQty() {
            return this.items.reduce((s, i) => s + (i.qty || 0), 0);
        },

        calcTotals() {
            this.subtotal = this.items.reduce((s, i) => s + this.lineTotal(i), 0);
            this.total    = this.subtotal;
            this.calcChange();
        },

        calcChange() {
            const paid = this.amountPaid || 0;
            this.balance = Math.max(0, this.total - paid);
            if (paid <= 0)               this.paymentStatus = 'unpaid';
            else if (paid >= this.total) this.paymentStatus = 'paid';
            else                         this.paymentStatus = 'partial';
        },

        fmt(v) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(v || 0)) + ' ' + CURRENCY;
        },

        fmtP(v) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(v || 0)) + ' ' + CURRENCY;
        },

        submitForm() {
            if (!this.isValid) return;
            document.getElementById('saleForm').submit();
        },
    };
}
</script>

@endsection
