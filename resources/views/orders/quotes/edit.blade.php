@extends('layouts.app')
@section('title', 'Modifier le devis')

@section('breadcrumb')
    <a href="{{ route('quotes.index') }}" class="hover:text-gray-600 transition-colors">Devis</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <a href="{{ route('quotes.show', $quote) }}" class="hover:text-gray-600 transition-colors">{{ $quote->reference }}</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Modifier</span>
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

$accessoryProductsJson = $accessoryProducts->map(fn($a) => [
    'id'             => $a->id,
    'name'           => $a->name,
    'price'          => $a->price ?? 0,
    'stock_quantity' => $a->stock_quantity ?? 0,
])->values();

$initialQuote = [
    'client_id'      => $quote->client_id,
    'gender'         => $quote->gender,
    'garments'       => $quote->garments ?? [],
    'accessories'    => $quote->accessories ?? [],
    'labor_cost'     => (float) $quote->labor_cost,
    'discount_type'  => $quote->discount_type,
    'discount_value' => (float) $quote->discount_value,
];
@endphp

<script>
const FABRICS             = {!! json_encode($fabricsJson) !!};
const CLIENTS              = {!! json_encode($clientsJson) !!};
const ACCESSORY_PRODUCTS  = {!! json_encode($accessoryProductsJson) !!};
const CURRENCY            = '{{ env("CURRENCY", "FCFA") }}';
const GARMENT_TYPES       = {!! json_encode($garmentTypes) !!};
const INITIAL_QUOTE       = {!! json_encode($initialQuote) !!};
</script>

<form method="POST" action="{{ route('quotes.update', $quote) }}" enctype="multipart/form-data"
      x-data="quoteForm(INITIAL_QUOTE)" id="quoteForm">
    @csrf
    @method('PUT')

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
                    <h2 class="text-lg font-display font-bold text-dark">Modifier le devis {{ $quote->reference }}</h2>
                    <p class="text-xs text-gray-400">Rectifiez ou complétez les informations avant renvoi au client</p>
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
                        <div x-data="searchSelect({ items: CLIENTS, labelKey: 'name', subKey: 'phone', placeholder: 'Rechercher un client...', inputName: 'client_id', onSelect: (id) => { clientId = id; }, initialId: clientId })" class="relative">
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

            {{-- ═══════════════════════════════════════════════
                 VÊTEMENTS (multi)
            ════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="shirt" class="w-4 h-4 text-blue-600"></i>
                        Vêtements
                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold"
                              x-text="garments.length"></span>
                    </h3>
                    <button type="button" x-on:click="addGarment()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xs font-semibold hover:bg-blue-100 transition-colors">
                        <i data-lucide="plus" style="width:13px;height:13px"></i>
                        Ajouter un vêtement
                    </button>
                </div>

                <div class="divide-y divide-gray-50">
                    <template x-for="(garment, gi) in garments" :key="garment._id">
                        <div class="p-5 space-y-4">

                            {{-- Header vêtement --}}
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-2 text-xs font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-full">
                                    <i data-lucide="shirt" style="width:12px;height:12px"></i>
                                    Vêtement <span x-text="gi + 1"></span>
                                </span>
                                <button type="button" x-show="garments.length > 1"
                                        x-on:click="removeGarment(gi)"
                                        class="inline-flex items-center gap-1 text-xs text-red-400 hover:text-red-600 hover:bg-red-50 px-2 py-1 rounded-lg transition-colors">
                                    <i data-lucide="trash-2" style="width:13px;height:13px"></i>
                                    Supprimer
                                </button>
                            </div>

                            {{-- Champs vêtement --}}
                            <div class="space-y-4">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-xs font-semibold text-gray-600">
                                            Type(s) de vêtement
                                            <span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full font-bold ml-1"
                                                  x-text="garment.garment_types.length"></span>
                                        </label>
                                        <button type="button" x-on:click="addGarmentType(gi)"
                                                class="inline-flex items-center gap-1 text-xs text-blue-600 hover:bg-blue-50 px-2 py-1 rounded-lg font-semibold transition-colors">
                                            <i data-lucide="plus" style="width:12px;height:12px"></i> Ajouter un type
                                        </button>
                                    </div>
                                    <input type="hidden" :name="`garments[${gi}][garment_type]`"
                                           :value="garment.garment_types.map(t => t.value).filter(v => v && v.trim()).join(', ')">
                                    <div class="space-y-2">
                                        <template x-for="(gt, gti) in garment.garment_types" :key="gt._id">
                                            <div class="space-y-1.5" :class="gt.mode === 'custom' ? 'p-2 rounded-xl bg-gray-50' : ''">
                                                <input type="hidden" :name="`garments[${gi}][garment_type_entries][${gti}][value]`" :value="gt.value">
                                                <input type="hidden" :name="`garments[${gi}][garment_type_entries][${gti}][price]`" :value="gt.mode === 'custom' ? (gt.price || 0) : 0">
                                                <input type="hidden" :name="`garments[${gi}][garment_type_entries][${gti}][mode]`" :value="gt.mode">
                                                <div class="flex items-center gap-2">
                                                    <div class="inline-flex rounded-lg bg-gray-100 p-0.5 shrink-0">
                                                        <button type="button"
                                                                x-on:click="gt.mode = 'list'; gt.value = ''; gt.price = 0; calcTotals()"
                                                                class="px-2.5 py-1 rounded-md text-xs font-semibold transition-all"
                                                                :class="gt.mode === 'list' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500'">
                                                            Liste
                                                        </button>
                                                        <button type="button"
                                                                x-on:click="gt.mode = 'custom'; gt.value = ''; gt.price = 0; calcTotals()"
                                                                class="px-2.5 py-1 rounded-md text-xs font-semibold transition-all"
                                                                :class="gt.mode === 'custom' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500'">
                                                            Manuel
                                                        </button>
                                                    </div>
                                                    <select x-show="gt.mode === 'list'" x-model="gt.value"
                                                            class="flex-1 px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                                        <option value="">— Choisir —</option>
                                                        <template x-for="[val, lbl] in Object.entries(GARMENT_TYPES)" :key="val">
                                                            <option :value="lbl" x-text="lbl" :selected="gt.value === lbl"></option>
                                                        </template>
                                                    </select>
                                                    <input x-show="gt.mode === 'custom'" type="text" x-model="gt.value"
                                                           placeholder="Ex : Kimono, Djellaba..."
                                                           class="flex-1 px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                                    <button type="button" x-show="garment.garment_types.length > 1"
                                                            x-on:click="removeGarmentType(gi, gti)"
                                                            class="w-9 h-9 rounded-lg hover:bg-red-50 text-gray-300 hover:text-red-500 flex items-center justify-center transition-colors shrink-0">
                                                        <i data-lucide="x" style="width:14px;height:14px"></i>
                                                    </button>
                                                </div>
                                                <div x-show="gt.mode === 'custom'" class="flex items-center justify-end gap-1.5 flex-wrap">
                                                    <span class="text-xs text-gray-400">Prix supplémentaire (par unité) :</span>
                                                    <input type="number" x-model.number="gt.price" x-on:input="calcTotals()"
                                                           min="0" step="100" placeholder="0"
                                                           class="w-28 px-2 py-1 rounded-lg border border-gray-200 text-xs text-right focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                                    <span class="text-xs text-gray-400 shrink-0" x-text="CURRENCY"></span>
                                                    <span class="text-xs text-gray-400" x-show="(garment.qty || 1) > 1">
                                                        (× <span x-text="garment.qty"></span> = <span class="font-semibold text-dark" x-text="fmt((parseFloat(gt.price) || 0) * (parseInt(garment.qty) || 1))"></span>)
                                                    </span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom du modèle</label>
                                        <input type="text" :name="`garments[${gi}][model_name]`" x-model="garment.model_name"
                                               placeholder="Ex : Robe soirée, Boubou..."
                                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Quantité</label>
                                        <input type="number" :name="`garments[${gi}][qty]`" x-model.number="garment.qty"
                                               x-on:input="calcTotals()"
                                               min="1" value="1"
                                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Description / détails de coupe</label>
                                <textarea :name="`garments[${gi}][model_description]`" x-model="garment.model_description"
                                          rows="2" placeholder="Coupe, style, finitions, mensurations particulières..."
                                          class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark resize-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"></textarea>
                            </div>

                            {{-- ── TISSUS (multi par vêtement) ── --}}
                            <div class="rounded-xl border border-gray-100 overflow-hidden">
                                <div class="px-4 py-3 bg-gray-50 flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-600 flex items-center gap-1.5">
                                        <i data-lucide="layers" style="width:13px;height:13px"></i>
                                        Tissus
                                        <span class="bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded-full text-xs font-bold"
                                              x-text="garment.fabrics.length"></span>
                                    </span>
                                    <button type="button" x-on:click="addFabric(gi)"
                                            class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:bg-indigo-50 px-2 py-1 rounded-lg font-semibold transition-colors">
                                        <i data-lucide="plus" style="width:12px;height:12px"></i> Tissu
                                    </button>
                                </div>

                                <div x-show="garment.fabrics.length === 0" class="px-4 py-4 text-center text-xs text-gray-400">
                                    Aucun tissu — cliquez sur « + Tissu » pour en ajouter.
                                </div>

                                <div class="divide-y divide-gray-50">
                                    <template x-for="(fabric, fi) in garment.fabrics" :key="fabric._id">
                                        <div class="p-4 space-y-3">

                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-semibold text-indigo-600"
                                                      x-text="`Tissu ${fi + 1}`"></span>
                                                <div class="flex items-center gap-3">
                                                    {{-- Toggle En stock / Hors stock --}}
                                                    <div class="inline-flex rounded-lg bg-gray-100 p-0.5">
                                                        <button type="button"
                                                                x-on:click="fabric.mode = 'stock'; fabric.fabric_name=''; fabric.fabric_price_per_meter=0; calcTotals()"
                                                                class="px-2.5 py-1 rounded-md text-xs font-semibold transition-all"
                                                                :class="fabric.mode === 'stock' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500'">
                                                            En stock
                                                        </button>
                                                        <button type="button"
                                                                x-on:click="fabric.mode = 'custom'; fabric.fabric_product_id=null; calcTotals()"
                                                                class="px-2.5 py-1 rounded-md text-xs font-semibold transition-all"
                                                                :class="fabric.mode === 'custom' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500'">
                                                            Hors stock
                                                        </button>
                                                    </div>
                                                    <button type="button" x-show="garment.fabrics.length > 1"
                                                            x-on:click="removeFabric(gi, fi)"
                                                            class="w-6 h-6 rounded-lg hover:bg-red-50 text-gray-300 hover:text-red-500 flex items-center justify-center transition-colors">
                                                        <i data-lucide="x" style="width:13px;height:13px"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Mode En stock --}}
                                            <div x-show="fabric.mode === 'stock'" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                <div class="sm:col-span-2"
                                                     x-data="fabricSelect(fabric)"
                                                     x-init="syncFabric(fabric)">
                                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tissu en stock</label>
                                                    <input type="hidden" :name="`garments[${gi}][fabrics][${fi}][fabric_product_id]`"
                                                           x-model="fabric.fabric_product_id">
                                                    <input type="hidden" :name="`garments[${gi}][fabrics][${fi}][mode]`" :value="fabric.mode">
                                                    <div class="relative">
                                                        <button type="button" x-on:click="fsOpen = !fsOpen"
                                                                class="w-full flex items-center justify-between px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm text-left focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                                            <span :class="fabric.fabric_product_id ? 'text-dark' : 'text-gray-400'" class="truncate text-xs"
                                                                  x-text="fabric.fabric_product_id ? (FABRICS.find(f=>f.id==fabric.fabric_product_id)?.name ?? '— Aucun —') : '— Aucun —'"></span>
                                                            <i data-lucide="chevrons-up-down" style="width:13px;height:13px" class="text-gray-400 shrink-0 ml-1"></i>
                                                        </button>
                                                        <div x-show="fsOpen" x-on:click.outside="fsOpen=false" x-transition
                                                             class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                                                            <div class="p-2 border-b border-gray-100">
                                                                <input type="text" x-model="fsSearch" x-on:input="filterFabrics()" placeholder="Filtrer..."
                                                                       class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-xs focus:outline-none">
                                                            </div>
                                                            <ul class="max-h-40 overflow-y-auto">
                                                                <li x-on:click="selectFabric(null, fabric); fsOpen=false"
                                                                    class="px-3 py-2 text-xs text-gray-400 hover:bg-gray-50 cursor-pointer">— Aucun —</li>
                                                                <template x-for="f in fsFiltered" :key="f.id">
                                                                    <li x-on:click="selectFabric(f, fabric); fsOpen=false"
                                                                        class="px-3 py-2.5 hover:bg-blue-50 cursor-pointer"
                                                                        :class="fabric.fabric_product_id == f.id ? 'bg-blue-50' : ''">
                                                                        <p class="text-xs font-semibold text-dark" x-text="f.name"></p>
                                                                        <p class="text-xs text-gray-400"
                                                                           x-text="f.price_per_meter.toLocaleString('fr-FR') + ' FCFA/m — ' + f.available_meters + 'm dispo'"></p>
                                                                    </li>
                                                                </template>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Mètres</label>
                                                    <input type="number" :name="`garments[${gi}][fabrics][${fi}][fabric_meters]`"
                                                           x-model.number="fabric.fabric_meters" x-on:input="calcTotals()"
                                                           min="0" step="0.5" placeholder="0.0"
                                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                                </div>
                                            </div>

                                            {{-- Mode Hors stock --}}
                                            <div x-show="fabric.mode === 'custom'" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom du tissu</label>
                                                    <input type="text" :name="`garments[${gi}][fabrics][${fi}][fabric_name]`"
                                                           x-model="fabric.fabric_name"
                                                           placeholder="Ex : Bazin riche..."
                                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prix / mètre</label>
                                                    <div class="flex items-center gap-1">
                                                        <input type="number" :name="`garments[${gi}][fabrics][${fi}][fabric_price_per_meter]`"
                                                               x-model.number="fabric.fabric_price_per_meter" x-on:input="calcTotals()"
                                                               min="0" step="100" placeholder="0"
                                                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                                        <span class="text-xs text-gray-400 shrink-0" x-text="CURRENCY"></span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Mètres</label>
                                                    <input type="number" :name="`garments[${gi}][fabrics][${fi}][fabric_meters]`"
                                                           x-model.number="fabric.fabric_meters" x-on:input="calcTotals()"
                                                           min="0" step="0.5" placeholder="0.0"
                                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                                </div>
                                            </div>

                                            {{-- Couleur (commun aux deux modes) --}}
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Couleur / précision</label>
                                                <input type="text" :name="`garments[${gi}][fabrics][${fi}][fabric_color]`"
                                                       x-model="fabric.fabric_color"
                                                       placeholder="Ex : Blanc cassé, imprimé wax..."
                                                       class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                            </div>

                                        </div>
                                    </template>
                                </div>
                            </div>
                            {{-- fin tissus --}}

                        </div>
                    </template>
                </div>
            </div>
            {{-- fin vêtements --}}

            {{-- Accessoires (communs au devis) --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="puzzle" class="w-4 h-4 text-blue-600"></i>
                        Accessoires
                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-bold"
                              x-text="accessories.length"></span>
                    </h3>
                    <button type="button" x-on:click="addAccessory()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-50 text-purple-700 text-xs font-semibold hover:bg-purple-100 transition-colors">
                        <i data-lucide="plus" style="width:13px;height:13px"></i>
                        Ajouter
                    </button>
                </div>

                <div x-show="accessories.length === 0" class="px-5 py-6 text-center text-gray-400 text-xs">
                    Boutons, fermetures, broderies, etc. — Ajoutez les accessoires inclus dans le devis.
                </div>

                <div class="divide-y divide-gray-50">
                    <template x-for="(acc, ai) in accessories" :key="acc._id">
                        <div class="p-4 space-y-3">

                            {{-- Toggle En stock / Nouveau --}}
                            <div class="flex items-center justify-between">
                                <div class="inline-flex rounded-lg bg-gray-100 p-0.5">
                                    <button type="button"
                                            x-on:click="acc.mode = 'stock'; acc.name=''; acc.price=0; calcTotals()"
                                            class="px-2.5 py-1 rounded-md text-xs font-semibold transition-all"
                                            :class="acc.mode === 'stock' ? 'bg-white text-purple-700 shadow-sm' : 'text-gray-500'">
                                        En stock
                                    </button>
                                    <button type="button"
                                            x-on:click="acc.mode = 'custom'; acc.product_id=null; acc.name=''; acc.price=0; calcTotals()"
                                            class="px-2.5 py-1 rounded-md text-xs font-semibold transition-all"
                                            :class="acc.mode === 'custom' ? 'bg-white text-purple-700 shadow-sm' : 'text-gray-500'">
                                        Nouveau
                                    </button>
                                </div>
                                <button type="button" x-on:click="removeAccessory(ai)"
                                        class="w-6 h-6 rounded-lg hover:bg-red-50 text-gray-300 hover:text-red-500 flex items-center justify-center transition-colors">
                                    <i data-lucide="x" style="width:13px;height:13px"></i>
                                </button>
                            </div>

                            {{-- Mode En stock : sélection depuis catalogue --}}
                            <div x-show="acc.mode === 'stock'" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <input type="hidden" :name="`accessories[${ai}][mode]`" :value="acc.mode">
                                <div class="sm:col-span-2"
                                     x-data="accSelect(acc)"
                                     x-init="asFiltered = ACCESSORY_PRODUCTS">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Accessoire en stock</label>
                                    <input type="hidden" :name="`accessories[${ai}][product_id]`" x-model="acc.product_id">
                                    <input type="hidden" :name="`accessories[${ai}][name]`" x-model="acc.name">
                                    <div class="relative">
                                        <button type="button" x-on:click="asOpen = !asOpen"
                                                class="w-full flex items-center justify-between px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm text-left focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                            <span :class="acc.product_id ? 'text-dark' : 'text-gray-400'" class="truncate text-xs"
                                                  x-text="acc.product_id ? (ACCESSORY_PRODUCTS.find(p=>p.id==acc.product_id)?.name ?? '— Aucun —') : '— Choisir un accessoire —'"></span>
                                            <i data-lucide="chevrons-up-down" style="width:13px;height:13px" class="text-gray-400 shrink-0 ml-1"></i>
                                        </button>
                                        <div x-show="asOpen" x-on:click.outside="asOpen=false" x-transition
                                             class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                                            <div class="p-2 border-b border-gray-100">
                                                <input type="text" x-model="asSearch" x-on:input="filterAcc()"
                                                       placeholder="Filtrer..."
                                                       class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-xs focus:outline-none">
                                            </div>
                                            <ul class="max-h-40 overflow-y-auto">
                                                <li x-on:click="selectAcc(null, acc); asOpen=false"
                                                    class="px-3 py-2 text-xs text-gray-400 hover:bg-gray-50 cursor-pointer">— Aucun —</li>
                                                <template x-for="p in asFiltered" :key="p.id">
                                                    <li x-on:click="selectAcc(p, acc); asOpen=false"
                                                        class="px-3 py-2.5 hover:bg-purple-50 cursor-pointer"
                                                        :class="acc.product_id == p.id ? 'bg-purple-50' : ''">
                                                        <p class="text-xs font-semibold text-dark" x-text="p.name"></p>
                                                        <p class="text-xs text-gray-400"
                                                           x-text="p.price.toLocaleString('fr-FR') + ' FCFA — ' + p.stock_quantity + ' en stock'"></p>
                                                    </li>
                                                </template>
                                                <li x-show="asFiltered.length === 0" class="px-3 py-4 text-center text-gray-400 text-xs">Aucun résultat</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Quantité</label>
                                    <input type="number" :name="`accessories[${ai}][qty]`"
                                           x-model.number="acc.qty" x-on:input="calcTotals()"
                                           min="1" placeholder="1"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                    <input type="hidden" :name="`accessories[${ai}][price]`"
                                           :value="acc.product_id ? (ACCESSORY_PRODUCTS.find(p=>p.id==acc.product_id)?.price ?? 0) : 0">
                                    <p class="text-xs text-gray-400 mt-1 text-right"
                                       x-show="acc.product_id"
                                       x-text="'Prix : ' + (ACCESSORY_PRODUCTS.find(p=>p.id==acc.product_id)?.price ?? 0).toLocaleString('fr-FR') + ' FCFA/u'"></p>
                                </div>
                            </div>

                            {{-- Mode Nouveau : saisie libre --}}
                            <div x-show="acc.mode === 'custom'" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom</label>
                                    <input type="text" :name="`accessories[${ai}][name]`"
                                           x-model="acc.name" placeholder="Ex : Boutons dorés..."
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Quantité</label>
                                    <input type="number" :name="`accessories[${ai}][qty]`"
                                           x-model.number="acc.qty" x-on:input="calcTotals()"
                                           min="1" placeholder="1"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prix unitaire</label>
                                    <div class="flex items-center gap-1">
                                        <input type="number" :name="`accessories[${ai}][price]`"
                                               x-model.number="acc.price" x-on:input="calcTotals()"
                                               min="0" step="100" placeholder="0"
                                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                        <span class="text-xs text-gray-400 shrink-0" x-text="CURRENCY"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Sous-total ligne --}}
                            <div class="flex justify-end">
                                <span class="text-xs text-gray-400">
                                    Sous-total :
                                    <strong class="text-dark" x-text="fmt(accLineTotal(acc))"></strong>
                                </span>
                            </div>

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
                        <input type="date" name="valid_until" value="{{ old('valid_until', optional($quote->valid_until)->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Délai de livraison estimé</label>
                        <input type="date" name="delivery_date" value="{{ old('delivery_date', optional($quote->delivery_date)->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Notes / conditions</label>
                    <textarea name="notes" rows="2" placeholder="Conditions particulières, acompte requis, délai de retouches..."
                              class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark resize-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">{{ old('notes', $quote->notes) }}</textarea>
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
                            <span>Vêtements</span>
                            <span class="font-semibold text-blue-600 text-xs"
                                  x-text="garments.length + ' article' + (garments.length > 1 ? 's' : '')"></span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Tissu total</span>
                            <span class="font-semibold text-dark" x-text="fmt(fabricCost)"></span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Types de vêtement (suppl.)</span>
                            <span class="font-semibold text-dark" x-text="fmt(garmentTypeCost)"></span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Accessoires</span>
                            <span class="font-semibold text-dark" x-text="fmt(accessoriesCost)"></span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span>Confection <span class="text-red-400">*</span></span>
                            <div class="flex items-center gap-1">
                                <input type="number" name="labor_cost" x-model.number="laborCost" x-on:input="calcTotals()"
                                       min="0" step="500" placeholder="0" required
                                       class="w-28 px-2 py-1 rounded-lg border border-gray-200 text-xs text-right focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                <span class="text-xs text-gray-400" x-text="CURRENCY"></span>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-2 flex items-center justify-between text-sm text-gray-500">
                            <span>Remise</span>
                            <div class="flex items-center gap-1">
                                <input type="hidden" name="discount_type" :value="discountType">
                                <div class="inline-flex rounded-lg bg-gray-100 p-0.5">
                                    <button type="button" x-on:click="discountType = 'fixed'; calcTotals()"
                                            class="px-2 py-0.5 rounded-md text-xs font-semibold transition-all"
                                            :class="discountType === 'fixed' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500'"
                                            x-text="CURRENCY">
                                    </button>
                                    <button type="button" x-on:click="discountType = 'percent'; calcTotals()"
                                            class="px-2 py-0.5 rounded-md text-xs font-semibold transition-all"
                                            :class="discountType === 'percent' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500'">
                                        %
                                    </button>
                                </div>
                                <input type="number" name="discount_value" x-model.number="discountValue" x-on:input="calcTotals()"
                                       min="0" :max="discountType === 'percent' ? 100 : null" :step="discountType === 'percent' ? 1 : 500" placeholder="0"
                                       class="w-20 px-2 py-1 rounded-lg border border-gray-200 text-xs text-right focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            </div>
                        </div>
                        <div class="flex justify-between text-sm" x-show="discountAmount > 0">
                            <span class="text-gray-500">Montant de la remise</span>
                            <span class="font-semibold text-red-500">− <span x-text="fmt(discountAmount)"></span></span>
                        </div>
                        <div class="border-t border-gray-100 pt-2 flex justify-between">
                            <span class="text-sm font-display font-bold text-dark">TOTAL ESTIMÉ</span>
                            <span class="text-xl font-display font-bold text-blue-600" x-text="fmt(total)"></span>
                        </div>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-gray-50">
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 active:scale-95 transition-all shadow-sm">
                            <i data-lucide="save" style="width:16px;height:16px"></i>
                            Enregistrer les modifications
                        </button>
                        <a href="{{ route('quotes.show', $quote) }}"
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
// ── Composant dropdown accessoire en stock ───────────────────────────────────
function accSelect(acc) {
    return {
        asOpen: false,
        asSearch: '',
        asFiltered: ACCESSORY_PRODUCTS,
        filterAcc() {
            const q = this.asSearch.toLowerCase();
            this.asFiltered = ACCESSORY_PRODUCTS.filter(p => p.name.toLowerCase().includes(q));
        },
        selectAcc(p, acc) {
            if (!p) {
                acc.product_id = null;
                acc.price = 0;
                acc.name = '';
            } else {
                acc.product_id = p.id;
                acc.price = p.price;
                acc.name = p.name;
            }
            const form = Alpine.$data(document.getElementById('quoteForm'));
            if (form) form.calcTotals();
        },
    };
}

// ── Composant dropdown tissu en stock (par fabric slot) ──────────────────────
function fabricSelect(fabric) {
    return {
        fsOpen: false,
        fsSearch: '',
        fsFiltered: FABRICS,
        syncFabric(f) { this.fsFiltered = FABRICS; },
        filterFabrics() {
            const q = this.fsSearch.toLowerCase();
            this.fsFiltered = FABRICS.filter(f =>
                f.name.toLowerCase().includes(q)
            );
        },
        selectFabric(f, fabric) {
            if (!f) {
                fabric.fabric_product_id = null;
            } else {
                fabric.fabric_product_id = f.id;
                // Pré-remplir le prix pour affichage (calcul fait côté Alpine)
                fabric._stockPrice = f.price_per_meter;
            }
            this.$dispatch('fabric-selected');
            // Recalcul global
            const form = Alpine.$data(document.getElementById('quoteForm'));
            if (form) form.calcTotals();
        },
    };
}

// ── Composant searchSelect (clients) ────────────────────────────────────────
function searchSelect({ items, labelKey, subKey, placeholder, inputName, onSelect, initialId }) {
    return {
        items, labelKey, subKey, placeholder, inputName, onSelect,
        open: false, search: '', selectedId: initialId || '', filtered: [],
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

// ── Composant principal quoteForm ─────────────────────────────────────────
// `initial` (optionnel) permet de pré-remplir le formulaire depuis un devis existant (édition).
function quoteForm(initial = null) {
    let _id = 0;
    const uid = () => ++_id;

    function newFabric(data = {}) {
        return { _id: uid(), mode: data.mode || 'stock', fabric_product_id: data.fabric_product_id ?? null, _stockPrice: 0,
                 fabric_name: data.fabric_name || '', fabric_price_per_meter: data.fabric_price_per_meter || 0,
                 fabric_meters: data.fabric_meters || 0, fabric_color: data.fabric_color || '' };
    }
    function newGarmentType(data = {}) {
        return { _id: uid(), mode: data.mode || 'list', value: data.value || '', price: data.price || 0 };
    }
    function newGarment(data = {}) {
        // Compat devis anciens/mobiles : si aucune entrée structurée n'est disponible,
        // on reconstruit depuis la chaîne combinée `garment_type` (ex : "Robe, Ensemble").
        let typeEntries = data.garment_type_entries;
        if ((!typeEntries || !typeEntries.length) && data.garment_type) {
            typeEntries = String(data.garment_type).split(',')
                .map(v => v.trim()).filter(v => v)
                .map(v => ({ mode: Object.values(GARMENT_TYPES).includes(v) ? 'list' : 'custom', value: v, price: 0 }));
        }
        return {
            _id: uid(),
            garment_types: (typeEntries && typeEntries.length) ? typeEntries.map(e => newGarmentType(e)) : [newGarmentType()],
            model_name: data.model_name || '',
            model_description: data.model_description || '',
            qty: data.qty || 1,
            fabrics: (data.fabrics && data.fabrics.length) ? data.fabrics.map(f => newFabric(f)) : [newFabric()],
        };
    }

    const initGarments = (initial && initial.garments && initial.garments.length)
        ? initial.garments.map(g => newGarment(g))
        : [newGarment()];

    const initAccessories = ((initial && initial.accessories) || []).map(a => ({
        _id: uid(), mode: a.mode || 'stock', product_id: a.product_id ?? null,
        name: a.name || '', qty: a.qty || 1, price: a.price || 0,
    }));

    return {
        clientId: (initial && initial.client_id) || '', gender: (initial && initial.gender) || 'femme',
        garments: initGarments,
        accessories: initAccessories, accCounter: 0,
        laborCost: (initial && initial.labor_cost) || 0, fabricCost: 0, garmentTypeCost: 0, accessoriesCost: 0, total: 0,
        discountType: (initial && initial.discount_type) || 'fixed', discountValue: (initial && initial.discount_value) || 0, discountAmount: 0,

        init() {
            this.calcTotals();
        },

        // ── Vêtements ──
        addGarment() {
            this.garments.push(newGarment());
            this.$nextTick(() => lucide.createIcons());
        },
        removeGarment(gi) {
            this.garments.splice(gi, 1);
            this.calcTotals();
        },

        // ── Types de vêtement ──
        addGarmentType(gi) {
            this.garments[gi].garment_types.push(newGarmentType());
            this.$nextTick(() => lucide.createIcons());
        },
        removeGarmentType(gi, gti) {
            this.garments[gi].garment_types.splice(gti, 1);
            this.calcTotals();
        },

        // ── Tissus ──
        addFabric(gi) {
            this.garments[gi].fabrics.push(newFabric());
            this.$nextTick(() => lucide.createIcons());
        },
        removeFabric(gi, fi) {
            this.garments[gi].fabrics.splice(fi, 1);
            this.calcTotals();
        },

        // ── Accessoires ──
        addAccessory() {
            this.accessories.push({
                _id: uid(), mode: 'stock',
                product_id: null, name: '', qty: 1, price: 0
            });
            this.$nextTick(() => lucide.createIcons());
        },
        removeAccessory(ai) {
            this.accessories.splice(ai, 1);
            this.calcTotals();
        },

        // Prix effectif d'une ligne accessoire
        accLineTotal(acc) {
            const qty = parseInt(acc.qty) || 1;
            if (acc.mode === 'stock' && acc.product_id) {
                const p = ACCESSORY_PRODUCTS.find(p => p.id == acc.product_id);
                return (p?.price ?? 0) * qty;
            }
            return (parseFloat(acc.price) || 0) * qty;
        },

        // ── Calcul totaux ──
        calcTotals() {
            let fc = 0;
            let gtc = 0;
            for (const g of this.garments) {
                for (const f of g.fabrics) {
                    const meters = parseFloat(f.fabric_meters) || 0;
                    if (f.mode === 'stock' && f.fabric_product_id) {
                        const product = FABRICS.find(p => p.id == f.fabric_product_id);
                        fc += (product?.price_per_meter ?? 0) * meters;
                    } else if (f.mode === 'custom') {
                        fc += (parseFloat(f.fabric_price_per_meter) || 0) * meters;
                    }
                }
                const gQty = parseInt(g.qty) || 1;
                for (const gt of g.garment_types) {
                    if (gt.mode === 'custom') {
                        gtc += (parseFloat(gt.price) || 0) * gQty;
                    }
                }
            }
            this.fabricCost = fc;
            this.garmentTypeCost = gtc;
            this.accessoriesCost = this.accessories.reduce((s, a) => s + this.accLineTotal(a), 0);

            const subtotal = this.fabricCost + (this.laborCost || 0) + this.accessoriesCost + this.garmentTypeCost;

            let discount = 0;
            const discountValue = parseFloat(this.discountValue) || 0;
            if (this.discountType === 'percent') {
                discount = subtotal * (Math.min(discountValue, 100) / 100);
            } else {
                discount = discountValue;
            }
            discount = Math.min(Math.max(discount, 0), subtotal);

            this.discountAmount = discount;
            this.total = subtotal - discount;
        },

        fmt(v) { return new Intl.NumberFormat('fr-FR').format(Math.round(v || 0)) + ' ' + CURRENCY; },
    };
}
</script>
@endsection
