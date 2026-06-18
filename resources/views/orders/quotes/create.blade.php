@extends('layouts.app')
@section('title', 'Nouveau devis')

@section('breadcrumb')
    <a href="{{ route('quotes.index') }}" class="hover:text-gray-600 transition-colors">Devis</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Nouveau devis</span>
@endsection

@section('content')

@php
$fabricsJson = $fabrics->map(fn($f) => [
    'id'              => $f->id,
    'name'            => $f->name,
    'price_per_meter' => $f->price_per_meter,
    'available_meters'=> $f->available_meters,
])->values();

$clientsJson = $clients->map(fn($c) => [
    'id'    => $c->id,
    'name'  => $c->full_name,
    'phone' => $c->phone,
])->values();
@endphp

<script>
const FABRICS  = {!! json_encode($fabricsJson) !!};
const CLIENTS  = {!! json_encode($clientsJson) !!};
const CURRENCY = '{{ env("CURRENCY", "FCFA") }}';
</script>

<form method="POST" action="{{ route('quotes.store') }}" enctype="multipart/form-data"
      x-data="quoteForm()" id="quoteForm">
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

        <div class="lg:col-span-2 space-y-5">

            {{-- En-tête --}}
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-2xl bg-blue-50 flex items-center justify-center">
                    <i data-lucide="file-text" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-display font-bold text-dark">Nouveau devis</h2>
                    <p class="text-xs text-gray-400">Préparez un devis à soumettre au client avant de passer commande</p>
                </div>
            </div>

            {{-- Client --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-blue-600"></i> Client
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Client <span class="text-red-400">*</span></label>
                        <div x-data="searchSelect({ items: CLIENTS, labelKey: 'name', subKey: 'phone', placeholder: 'Rechercher un client...', inputName: 'client_id', onSelect: (id) => { clientId = id; } })" class="relative">
                            <input type="hidden" name="client_id" x-model="selectedId">
                            <button type="button" x-on:click="open = !open"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm text-left focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                <span :class="selectedId ? 'text-dark' : 'text-gray-400'"
                                      x-text="selectedId ? (items.find(i=>i.id==selectedId)?.name + ' — ' + items.find(i=>i.id==selectedId)?.phone) : placeholder"></span>
                                <i data-lucide="chevrons-up-down" style="width:14px;height:14px" class="text-gray-400 shrink-0 ml-2"></i>
                            </button>
                            <div x-show="open" x-on:click.outside="open=false" x-transition
                                 class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                                <div class="p-2 border-b border-gray-100">
                                    <input type="text" x-model="search" x-on:input="filterItems" placeholder="Taper pour filtrer..."
                                           class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm focus:outline-none">
                                </div>
                                <ul class="max-h-48 overflow-y-auto">
                                    <template x-for="item in filtered" :key="item.id">
                                        <li x-on:click="select(item)" class="flex items-center gap-3 px-3 py-2.5 hover:bg-blue-50 cursor-pointer transition-colors"
                                            :class="selectedId == item.id ? 'bg-blue-50' : ''">
                                            <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs shrink-0"
                                                 x-text="item.name.charAt(0).toUpperCase()"></div>
                                            <div>
                                                <p class="text-sm font-semibold text-dark" x-text="item.name"></p>
                                                <p class="text-xs text-gray-400" x-text="item.phone"></p>
                                            </div>
                                        </li>
                                    </template>
                                    <li x-show="filtered.length === 0" class="px-3 py-4 text-center text-gray-400 text-sm">Aucun résultat</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Genre</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['homme' => 'Homme', 'femme' => 'Femme', 'enfant' => 'Enfant'] as $val => $lbl)
                                <label class="flex items-center justify-center py-2 rounded-xl border cursor-pointer text-xs font-semibold transition-all"
                                       :class="gender === '{{ $val }}' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                                    <input type="radio" name="gender" value="{{ $val }}" x-model="gender" class="sr-only">
                                    {{ $lbl }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modèle --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="shirt" class="w-4 h-4 text-blue-600"></i> Modèle & vêtement
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Type de vêtement</label>
                        <select name="garment_type"
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                            <option value="">— Choisir —</option>
                            @foreach($garmentTypes as $val => $lbl)
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom du modèle</label>
                        <input type="text" name="model_name" value="{{ old('model_name') }}"
                               placeholder="Ex : Robe soirée, Boubou homme..."
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Description</label>
                    <textarea name="model_description" rows="3" placeholder="Détails de coupe, style, finitions..."
                              class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark resize-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">{{ old('model_description') }}</textarea>
                </div>
            </div>

            {{-- Tissu --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4 text-blue-600"></i> Tissu (optionnel)
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tissu en stock</label>
                        <div x-data="searchSelect({ items: [{id:'',name:'— Aucun —',sub:''}].concat(FABRICS.map(f=>({id:f.id,name:f.name,sub:f.price_per_meter.toLocaleString('fr-FR')+' FCFA/m — '+f.available_meters+'m dispo'}))), labelKey: 'name', subKey: 'sub', placeholder: 'Rechercher un tissu...', inputName: 'fabric_product_id', onSelect: (id) => { fabricId = id; calcTotals(); } })" class="relative">
                            <input type="hidden" name="fabric_product_id" x-model="selectedId">
                            <button type="button" x-on:click="open = !open"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm text-left focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                <span :class="selectedId ? 'text-dark' : 'text-gray-400'" class="truncate"
                                      x-text="selectedId ? items.find(i=>i.id==selectedId)?.name : '— Aucun —'"></span>
                                <i data-lucide="chevrons-up-down" style="width:14px;height:14px" class="text-gray-400 shrink-0 ml-2"></i>
                            </button>
                            <div x-show="open" x-on:click.outside="open=false" x-transition
                                 class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                                <div class="p-2 border-b border-gray-100">
                                    <input type="text" x-model="search" x-on:input="filterItems" placeholder="Filtrer..."
                                           class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm focus:outline-none">
                                </div>
                                <ul class="max-h-48 overflow-y-auto">
                                    <template x-for="item in filtered" :key="item.id">
                                        <li x-on:click="select(item)" class="px-3 py-2.5 hover:bg-blue-50 cursor-pointer transition-colors">
                                            <p class="text-sm font-semibold text-dark" x-text="item.name"></p>
                                            <p x-show="item.sub" class="text-xs text-gray-400" x-text="item.sub"></p>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Mètres</label>
                        <input type="number" name="fabric_meters" x-model.number="fabricMeters" x-on:input="calcTotals"
                               min="0" step="0.5" placeholder="0.0"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Couleur / précision tissu</label>
                    <input type="text" name="fabric_color" value="{{ old('fabric_color') }}"
                           placeholder="Ex : Blanc cassé, imprimé wax..."
                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                </div>
            </div>

            {{-- Accessoires --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="puzzle" class="w-4 h-4 text-blue-600"></i>
                        Accessoires
                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-bold"
                              x-text="accessories.length"></span>
                    </h3>
                    <button type="button" x-on:click="addAccessory"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-50 text-purple-700 text-xs font-semibold hover:bg-purple-100 transition-colors">
                        <i data-lucide="plus" style="width:13px;height:13px"></i>
                        Ajouter
                    </button>
                </div>

                <div x-show="accessories.length === 0" class="px-5 py-6 text-center text-gray-400 text-xs">
                    Boutons, fermetures, broderies, etc. — Ajoutez les accessoires inclus dans le devis.
                </div>

                <div class="divide-y divide-gray-50">
                    <template x-for="(acc, index) in accessories" :key="acc.id">
                        <div class="px-5 py-3 flex items-center gap-3">
                            <div class="flex-1">
                                <input type="text" :name="`accessories[${index}][name]`"
                                       x-model="acc.name" placeholder="Nom de l'accessoire"
                                       class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                            </div>
                            <div class="w-20">
                                <input type="number" :name="`accessories[${index}][qty]`"
                                       x-model.number="acc.qty" x-on:input="calcTotals"
                                       min="1" placeholder="Qté"
                                       class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                            </div>
                            <div class="w-28">
                                <input type="number" :name="`accessories[${index}][price]`"
                                       x-model.number="acc.price" x-on:input="calcTotals"
                                       min="0" step="100" placeholder="Prix"
                                       class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                            </div>
                            <button type="button" x-on:click="removeAccessory(index)"
                                    class="w-6 h-6 rounded-lg hover:bg-red-50 text-gray-300 hover:text-red-500 flex items-center justify-center transition-colors">
                                <i data-lucide="x" style="width:13px;height:13px"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <div x-show="accessories.length > 0" class="px-5 py-3 border-t border-gray-50 flex justify-end">
                    <span class="text-xs text-gray-500">Total accessoires :
                        <strong class="text-dark" x-text="fmt(accessoriesCost)"></strong>
                    </span>
                </div>
            </div>

            {{-- Validité & livraison --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-blue-600"></i> Validité & livraison
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Devis valide jusqu'au</label>
                        <input type="date" name="valid_until" value="{{ old('valid_until', now()->addDays(15)->format('Y-m-d')) }}"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Délai de livraison estimé</label>
                        <input type="date" name="delivery_date" value="{{ old('delivery_date') }}"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Notes / conditions</label>
                    <textarea name="notes" rows="2" placeholder="Conditions particulières, acompte requis, délai de retouches..."
                              class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark resize-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">{{ old('notes') }}</textarea>
                </div>
            </div>

        </div>

        {{-- Colonne droite : récapitulatif --}}
        <div class="space-y-5">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden sticky top-6">
                <div class="px-5 py-4 border-b border-gray-50">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-blue-600"></i> Récapitulatif
                    </h3>
                </div>
                <div class="p-5 space-y-4">

                    <div class="space-y-2">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Tissu</span>
                            <span class="font-semibold text-dark" x-text="fmt(fabricCost)"></span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Accessoires</span>
                            <span class="font-semibold text-dark" x-text="fmt(accessoriesCost)"></span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span>Main d'œuvre <span class="text-red-400">*</span></span>
                            <div class="flex items-center gap-1">
                                <input type="number" name="labor_cost" x-model.number="laborCost" x-on:input="calcTotals"
                                       value="{{ old('labor_cost', 0) }}" min="0" step="500" placeholder="0" required
                                       class="w-28 px-2 py-1 rounded-lg border border-gray-200 text-xs text-right focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                <span class="text-xs text-gray-400" x-text="CURRENCY"></span>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-2 flex justify-between">
                            <span class="text-sm font-display font-bold text-dark">TOTAL ESTIMÉ</span>
                            <span class="text-xl font-display font-bold text-blue-600" x-text="fmt(total)"></span>
                        </div>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-gray-50">
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 active:scale-95 transition-all shadow-sm">
                            <i data-lucide="file-text" style="width:16px;height:16px"></i>
                            Créer le devis
                        </button>
                        <a href="{{ route('quotes.index') }}"
                           class="w-full flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                            Annuler
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>

<script>
function searchSelect({ items, labelKey, subKey, placeholder, inputName, onSelect }) {
    return {
        items, labelKey, subKey, placeholder, inputName, onSelect,
        open: false, search: '', selectedId: '', filtered: [],
        init() {
            this.filtered = this.items;
            this.$watch('open', v => {
                if (v) { this.search = ''; this.filtered = this.items;
                    this.$nextTick(() => { this.$el.querySelector('input[type=text]')?.focus(); lucide.createIcons(); }); }
            });
        },
        filterItems() {
            const q = this.search.toLowerCase();
            this.filtered = this.items.filter(i => (i[this.labelKey]||'').toLowerCase().includes(q)||(i[this.subKey]||'').toLowerCase().includes(q));
        },
        select(item) { this.selectedId = item.id; this.open = false; if (this.onSelect) this.onSelect(item.id); },
    };
}

function quoteForm() {
    return {
        clientId: '', gender: 'femme', fabricId: '', fabricMeters: 0, laborCost: 0,
        fabricCost: 0, total: 0,
        accessories: [], accCounter: 0, accessoriesCost: 0,

        get selectedFabric() { return FABRICS.find(f => f.id == this.fabricId) || null; },

        addAccessory() {
            this.accessories.push({ id: ++this.accCounter, name: '', qty: 1, price: 0 });
            this.$nextTick(() => lucide.createIcons());
        },
        removeAccessory(index) {
            this.accessories.splice(index, 1);
            this.calcTotals();
        },

        calcTotals() {
            this.fabricCost = (this.fabricId && this.fabricMeters > 0 && this.selectedFabric)
                ? this.selectedFabric.price_per_meter * this.fabricMeters : 0;
            this.accessoriesCost = this.accessories.reduce((s, a) => s + ((a.price||0) * (a.qty||1)), 0);
            this.total = this.fabricCost + (this.laborCost || 0) + this.accessoriesCost;
        },

        fmt(v) { return new Intl.NumberFormat('fr-FR').format(Math.round(v || 0)) + ' ' + CURRENCY; },
    };
}
</script>
@endsection
