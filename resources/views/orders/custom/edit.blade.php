@extends('layouts.app')
@section('title', 'Modifier ' . $customOrder->reference)

@section('breadcrumb')
    <a href="{{ route('custom-orders.index') }}" class="hover:text-gray-600 transition-colors">Sur mesure</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <a href="{{ route('custom-orders.show', $customOrder) }}" class="hover:text-gray-600 transition-colors">{{ $customOrder->reference }}</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Modifier</span>
@endsection

@section('content')

@php
$fabricsJson = $fabrics->map(fn($f) => [
    'id'              => $f->id,
    'name'            => $f->name,
    'reference'       => $f->reference,
    'price_per_meter' => $f->price_per_meter,
    'available_meters'=> $f->available_meters,
    'color'           => $f->color,
])->values();

$clientsJson = $clients->map(fn($c) => [
    'id'           => $c->id,
    'name'         => $c->full_name,
    'phone'        => $c->phone,
    'measurements' => $c->measurements->map(fn($m) => [
        'id'         => $m->id,
        'label'      => $m->label ?: 'Mesures #' . $m->id,
        'is_default' => $m->is_default,
    ])->values(),
])->values();

$garmentTypes = [
    'robe'      => 'Robe',
    'costume'   => 'Costume',
    'pantalon'  => 'Pantalon',
    'chemise'   => 'Chemise',
    'boubou'    => 'Boubou',
    'ensemble'  => 'Ensemble',
    'autre'     => 'Autre',
];
@endphp

<script>
const FABRICS  = {!! json_encode($fabricsJson) !!};
const CLIENTS  = {!! json_encode($clientsJson) !!};
const CURRENCY = '{{ env("CURRENCY", "FCFA") }}';
const ORDER    = {
    clientId:      {{ $customOrder->client_id }},
    gender:        '{{ $customOrder->gender }}',
    fabricId:      {{ $customOrder->fabric_product_id ?? 'null' }},
    fabricMeters:  {{ $customOrder->fabric_meters ?? 0 }},
    laborCost:     {{ $customOrder->labor_cost ?? 0 }},
    deposit:       {{ $customOrder->deposit ?? 0 }},
    paymentMethod: '{{ $customOrder->payment_method ?? 'cash' }}',
    measurementId: {{ $customOrder->measurement_id ?? 'null' }},
};
</script>

<form method="POST" action="{{ route('custom-orders.update', $customOrder) }}"
      enctype="multipart/form-data"
      x-data="customOrderForm()"
      x-on:submit.prevent="submitForm"
      id="customOrderForm">
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

        {{-- ══════════ COLONNE GAUCHE ══════════ --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- En-tête --}}
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-2xl bg-purple-50 flex items-center justify-center">
                    <i data-lucide="edit-2" class="w-5 h-5 text-purple-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-display font-bold text-dark">Modifier la commande</h2>
                    <p class="text-xs font-mono text-gray-400">{{ $customOrder->reference }}</p>
                </div>
            </div>

            {{-- ── 1. Client + Genre --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-purple-600"></i>
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
                                inputName: 'client_id',
                                onSelect: (id) => { clientId = id; onClientChange(); }
                             })" class="relative"
                             x-init="selectedId = ORDER.clientId">
                            <input type="hidden" name="client_id" x-model="selectedId">
                            <button type="button" x-on:click="open = !open"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm text-left
                                           focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                <span :class="selectedId ? 'text-dark' : 'text-gray-400'"
                                      x-text="selectedId ? (items.find(i=>i.id==selectedId)?.name + ' — ' + items.find(i=>i.id==selectedId)?.phone) : placeholder"></span>
                                <i data-lucide="chevrons-up-down" style="width:14px;height:14px" class="text-gray-400 shrink-0 ml-2"></i>
                            </button>
                            <div x-show="open" x-on:click.outside="open=false" x-transition
                                 class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                                <div class="p-2 border-b border-gray-100">
                                    <input type="text" x-model="search" x-on:input="filterItems"
                                           placeholder="Taper pour filtrer..."
                                           class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                </div>
                                <ul class="max-h-48 overflow-y-auto">
                                    <template x-for="item in filtered" :key="item.id">
                                        <li x-on:click="select(item)"
                                            class="flex items-center gap-3 px-3 py-2.5 hover:bg-purple-50 cursor-pointer transition-colors"
                                            :class="selectedId == item.id ? 'bg-purple-50' : ''">
                                            <div class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-xs shrink-0"
                                                 x-text="item.name.charAt(0).toUpperCase()"></div>
                                            <div>
                                                <p class="text-sm font-semibold text-dark" x-text="item.name"></p>
                                                <p class="text-xs text-gray-400" x-text="item.phone"></p>
                                            </div>
                                            <i x-show="selectedId == item.id" data-lucide="check" style="width:14px;height:14px" class="text-purple-600 ml-auto shrink-0"></i>
                                        </li>
                                    </template>
                                    <li x-show="filtered.length === 0" class="px-3 py-4 text-center text-gray-400 text-sm">Aucun résultat</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Genre <span class="text-red-400">*</span></label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['homme' => 'Homme', 'femme' => 'Femme', 'enfant' => 'Enfant'] as $val => $lbl)
                                <label class="flex items-center justify-center py-2 rounded-xl border cursor-pointer text-xs font-semibold transition-all"
                                       :class="gender === '{{ $val }}' ? 'border-purple-500 bg-purple-50 text-purple-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                                    <input type="radio" name="gender" value="{{ $val }}" x-model="gender" class="sr-only">
                                    {{ $lbl }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 2. Modèle --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="shirt" class="w-4 h-4 text-purple-600"></i>
                    Modèle &amp; vêtement
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Type de vêtement <span class="text-red-400">*</span></label>
                        <select name="garment_type" required
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark
                                       focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                            <option value="">— Choisir —</option>
                            @foreach($garmentTypes as $val => $lbl)
                                <option value="{{ $val }}" {{ (old('garment_type', $customOrder->garment_type) === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom du modèle</label>
                        <input type="text" name="model_name"
                               value="{{ old('model_name', $customOrder->model_name) }}"
                               placeholder="Ex : Robe soirée, Costume 3 pièces..."
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark
                                      focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Description du modèle</label>
                    <textarea name="model_description" rows="3" placeholder="Détails de coupe, style, finitions souhaitées..."
                              class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark resize-none
                                     focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">{{ old('model_description', $customOrder->model_description) }}</textarea>
                </div>
                {{-- Photo existante --}}
                @if($customOrder->model_photo)
                <div class="flex items-center gap-4 p-3 bg-purple-50 rounded-xl border border-purple-100">
                    <img src="{{ asset('storage/' . $customOrder->model_photo) }}" class="w-16 h-16 rounded-lg object-cover border border-purple-200">
                    <div>
                        <p class="text-xs font-semibold text-purple-700">Photo actuelle</p>
                        <p class="text-xs text-gray-400 mt-0.5">Sélectionnez une nouvelle photo pour la remplacer</p>
                    </div>
                </div>
                @endif
                <div x-data="{ preview: null }">
                    <label class="block text-xs font-semibold text-gray-600 mb-2">{{ $customOrder->model_photo ? 'Remplacer la photo' : 'Photo du modèle (optionnel)' }}</label>
                    <label class="flex items-center gap-3 px-4 py-3 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-purple-400 hover:bg-purple-50 transition-all">
                        <i data-lucide="image-plus" class="w-5 h-5 text-gray-300 shrink-0"></i>
                        <span class="text-xs text-gray-400" x-text="preview ? 'Nouvelle photo sélectionnée ✓' : 'Cliquer pour choisir une photo (JPG, PNG — max 2 Mo)'"></span>
                        <input type="file" name="model_photo" accept="image/*" class="hidden"
                               x-on:change="const f=$event.target.files[0]; if(f){const r=new FileReader();r.onload=e=>preview=e.target.result;r.readAsDataURL(f);}">
                    </label>
                    <div x-show="preview" class="mt-2">
                        <img :src="preview" class="w-24 h-24 rounded-xl object-cover border border-purple-200">
                    </div>
                </div>
            </div>

            {{-- ── 3. Tissu --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4"
                 x-data="{ clientOwnsFabric: {{ $customOrder->fabric_product_id ? 'false' : 'false' }} }"
                 x-on:change.capture="if($event.target.name==='client_owns_fabric') { clientOwnsFabric=$event.target.checked; if(clientOwnsFabric){ fabricId=''; fabricMeters=0; calcTotals(); } }">

                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="layers" class="w-4 h-4 text-purple-600"></i>
                        Tissu
                    </h3>
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" name="client_owns_fabric" value="1"
                                   x-model="clientOwnsFabric" class="sr-only peer">
                            <div class="w-10 h-5 rounded-full transition-colors peer-checked:bg-purple-500 bg-gray-200"></div>
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                        </div>
                        <span class="text-xs font-semibold" :class="clientOwnsFabric ? 'text-purple-600' : 'text-gray-400'"
                              x-text="clientOwnsFabric ? 'Client apporte son tissu' : 'Tissu depuis le stock'"></span>
                    </label>
                </div>

                <div :class="clientOwnsFabric ? 'opacity-40 pointer-events-none select-none' : ''"
                     class="space-y-4 transition-opacity duration-200">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tissu en stock</label>
                            <div x-data="searchSelect({
                                    items: [{id:'',name:'— Aucun —',sub:''}].concat(FABRICS.map(f=>({id:f.id,name:f.name,sub:f.price_per_meter.toLocaleString('fr-FR')+' FCFA/m — '+f.available_meters+'m dispo'}))),
                                    labelKey: 'name',
                                    subKey: 'sub',
                                    placeholder: 'Rechercher un tissu...',
                                    inputName: 'fabric_product_id',
                                    onSelect: (id) => { fabricId = id; calcTotals(); }
                                 })"
                                 class="relative"
                                 x-init="selectedId = ORDER.fabricId">
                                <input type="hidden" name="fabric_product_id" x-model="selectedId">
                                <button type="button" x-on:click="open = !open"
                                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm text-left
                                               focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                    <span :class="selectedId ? 'text-dark' : 'text-gray-400'" class="truncate"
                                          x-text="selectedId ? (items.find(i=>i.id==selectedId)?.name ?? '— Sélectionné —') : '— Aucun —'"></span>
                                    <i data-lucide="chevrons-up-down" style="width:14px;height:14px" class="text-gray-400 shrink-0 ml-2"></i>
                                </button>
                                <div x-show="open" x-on:click.outside="open=false" x-transition
                                     class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                                    <div class="p-2 border-b border-gray-100">
                                        <input type="text" x-model="search" x-on:input="filterItems"
                                               placeholder="Taper pour filtrer..."
                                               class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm focus:outline-none">
                                    </div>
                                    <ul class="max-h-52 overflow-y-auto">
                                        <template x-for="item in filtered" :key="item.id">
                                            <li x-on:click="select(item)"
                                                class="px-3 py-2.5 hover:bg-purple-50 cursor-pointer transition-colors"
                                                :class="selectedId == item.id ? 'bg-purple-50' : ''">
                                                <p class="text-sm font-semibold text-dark" x-text="item.name"></p>
                                                <p x-show="item.sub" class="text-xs text-gray-400" x-text="item.sub"></p>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Mètres nécessaires</label>
                            <input type="number" name="fabric_meters" x-model.number="fabricMeters"
                                   x-on:input="calcTotals"
                                   value="{{ old('fabric_meters', $customOrder->fabric_meters) }}"
                                   min="0.5" step="0.5" placeholder="0.0"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right
                                          focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                        </div>
                    </div>
                    <div x-show="fabricId" x-transition class="flex items-center gap-4 bg-purple-50 rounded-xl px-4 py-3 text-xs">
                        <span class="text-gray-500">Stock :</span>
                        <span class="font-bold text-dark" x-text="selectedFabric ? selectedFabric.available_meters + ' m' : ''"></span>
                        <span class="text-gray-500 ml-2">Prix :</span>
                        <span class="font-bold text-dark" x-text="selectedFabric ? fmt(selectedFabric.price_per_meter) + '/m' : ''"></span>
                        <span class="text-gray-500 ml-2">Coût :</span>
                        <span class="font-bold text-purple-700" x-text="fmt(fabricCost)"></span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Couleur / précision tissu</label>
                        <input type="text" name="fabric_color"
                               value="{{ old('fabric_color', $customOrder->fabric_color) }}"
                               placeholder="Ex : Blanc cassé, rayé bleu marine..."
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark
                                      focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                    </div>
                </div>

                <div x-show="clientOwnsFabric" x-transition
                     class="flex items-center gap-3 px-4 py-3 bg-purple-50 rounded-xl border border-purple-100">
                    <i data-lucide="info" class="w-4 h-4 text-purple-500 shrink-0"></i>
                    <p class="text-xs text-purple-700 font-medium">Le client apporte son propre tissu.</p>
                </div>
            </div>

            {{-- ── 4. Mesures --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="ruler" class="w-4 h-4 text-purple-600"></i>
                    Mesures
                </h3>
                <div x-show="clientId">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Fiche de mesures</label>
                    <select name="measurement_id" x-model="measurementId"
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark
                                   focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                        <option value="">— Aucune fiche —</option>
                        <template x-for="m in clientMeasurements" :key="m.id">
                            <option :value="m.id" x-text="m.label + (m.is_default ? ' (par défaut)' : '')"></option>
                        </template>
                    </select>
                </div>
                <div x-show="!clientId" class="text-xs text-gray-400 italic">
                    Sélectionnez un client pour accéder aux mesures.
                </div>
            </div>

            {{-- ── 5. Production --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-purple-600"></i>
                    Production
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Couturier assigné</label>
                        <select name="assigned_to"
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark
                                       focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                            <option value="">— À assigner plus tard —</option>
                            @foreach($couturiers as $couturier)
                                <option value="{{ $couturier->id }}" {{ old('assigned_to', $customOrder->assigned_to) == $couturier->id ? 'selected' : '' }}>
                                    {{ $couturier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Date de livraison prévue</label>
                        <input type="date" name="delivery_date"
                               value="{{ old('delivery_date', $customOrder->delivery_date?->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark
                                      focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Notes internes</label>
                    <textarea name="notes" rows="2" placeholder="Instructions pour l'atelier..."
                              class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark resize-none
                                     focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">{{ old('notes', $customOrder->notes) }}</textarea>
                </div>
            </div>

        </div>

        {{-- ══════════ COLONNE DROITE ══════════ --}}
        <div class="space-y-5">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden sticky top-6">
                <div class="px-5 py-4 border-b border-gray-50">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-purple-600"></i>
                        Récapitulatif
                    </h3>
                </div>
                <div class="p-5 space-y-4">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Tissu</span>
                            <span class="font-semibold text-dark" x-text="fmt(fabricCost)"></span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span>Main d'œuvre <span class="text-red-400">*</span></span>
                            <div class="flex items-center gap-1">
                                <input type="number" name="labor_cost" x-model.number="laborCost"
                                       x-on:input="calcTotals"
                                       value="{{ old('labor_cost', $customOrder->labor_cost) }}"
                                       min="0" step="500" required
                                       class="w-28 px-2 py-1 rounded-lg border border-gray-200 text-xs text-right
                                              focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                                <span class="text-xs text-gray-400" x-text="CURRENCY"></span>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-2 flex justify-between">
                            <span class="text-sm font-display font-bold text-dark">TOTAL</span>
                            <span class="text-xl font-display font-bold text-purple-600" x-text="fmt(total)"></span>
                        </div>
                    </div>

                    <div class="space-y-3 pt-2 border-t border-gray-50">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Acompte</h4>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1.5">Mode de paiement</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach(['cash' => ['Espèces','banknote'], 'mobile_money' => ['Mobile','smartphone'], 'card' => ['Carte','credit-card'], 'credit' => ['Crédit','clock']] as $val => $info)
                                    <label class="flex items-center gap-2 px-3 py-2 rounded-xl border cursor-pointer text-xs font-semibold transition-all"
                                           :class="paymentMethod === '{{ $val }}' ? 'border-purple-500 bg-purple-50 text-purple-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                                        <input type="radio" name="payment_method" value="{{ $val }}" x-model="paymentMethod" class="sr-only">
                                        <i data-lucide="{{ $info[1] }}" style="width:13px;height:13px"></i>
                                        {{ $info[0] }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1.5">Acompte versé</label>
                            <div class="relative">
                                <input type="number" name="deposit" x-model.number="deposit"
                                       x-on:input="calcChange"
                                       value="{{ old('deposit', $customOrder->deposit) }}"
                                       min="0" step="500" placeholder="0"
                                       class="w-full px-3 py-2.5 pr-16 rounded-xl border border-gray-200 text-sm font-bold text-dark text-right
                                              focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400 pointer-events-none" x-text="CURRENCY"></span>
                            </div>
                            <button type="button" x-on:click="deposit = total; calcChange()"
                                    class="mt-2 w-full text-xs text-purple-600 font-semibold py-1.5 rounded-lg border border-purple-200 bg-purple-50 hover:bg-purple-100 transition-colors">
                                Paiement intégral
                            </button>
                        </div>
                        <div x-show="balance > 0" x-transition
                             class="flex justify-between px-3 py-2.5 rounded-xl bg-orange-50 border border-orange-100">
                            <span class="text-xs font-semibold text-orange-700">Reste à payer</span>
                            <span class="text-sm font-bold text-orange-700" x-text="fmt(balance)"></span>
                        </div>
                        <div class="flex justify-center">
                            <span class="badge-status text-xs px-3 py-1"
                                  :class="{
                                      'bg-green-50 text-green-700': paymentStatus === 'paid',
                                      'bg-yellow-50 text-yellow-700': paymentStatus === 'partial',
                                      'bg-red-50 text-red-700': paymentStatus === 'unpaid'
                                  }">
                                <span x-show="paymentStatus === 'paid'">✓ Soldé</span>
                                <span x-show="paymentStatus === 'partial'">◑ Acompte versé</span>
                                <span x-show="paymentStatus === 'unpaid'">○ Sans acompte</span>
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-gray-50">
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-purple-600 text-white
                                       text-sm font-bold hover:bg-purple-700 active:scale-95 transition-all shadow-sm shadow-purple-500/20">
                            <i data-lucide="save" style="width:16px;height:16px"></i>
                            Enregistrer les modifications
                        </button>
                        <a href="{{ route('custom-orders.show', $customOrder) }}"
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
function searchSelect({ items, labelKey, subKey, placeholder, inputName, onSelect }) {
    return {
        items, labelKey, subKey, placeholder, inputName, onSelect,
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

function customOrderForm() {
    return {
        clientId:      ORDER.clientId || '',
        gender:        ORDER.gender   || 'femme',
        fabricId:      ORDER.fabricId || '',
        fabricMeters:  ORDER.fabricMeters || 0,
        laborCost:     ORDER.laborCost || 0,
        deposit:       ORDER.deposit  || 0,
        paymentMethod: ORDER.paymentMethod || 'cash',
        measurementId: ORDER.measurementId || '',

        fabricCost: 0,
        total:      0,
        balance:    0,
        paymentStatus: 'unpaid',

        get clientMeasurements() {
            const c = CLIENTS.find(c => c.id == this.clientId);
            return c ? c.measurements : [];
        },
        get selectedFabric() {
            return FABRICS.find(f => f.id == this.fabricId) || null;
        },

        init() {
            this.$watch('fabricId', () => this.calcTotals());
            this.$watch('fabricMeters', () => this.calcTotals());
            this.$watch('laborCost', () => this.calcTotals());
            this.calcTotals();
        },

        onClientChange() {
            this.measurementId = '';
            const c = CLIENTS.find(c => c.id == this.clientId);
            if (c) {
                const def = c.measurements.find(m => m.is_default);
                if (def) this.measurementId = def.id;
            }
        },

        calcTotals() {
            this.fabricCost = (this.fabricId && this.fabricMeters > 0 && this.selectedFabric)
                ? this.selectedFabric.price_per_meter * this.fabricMeters : 0;
            this.total = this.fabricCost + (this.laborCost || 0);
            this.calcChange();
        },

        calcChange() {
            const dep = this.deposit || 0;
            this.balance = Math.max(0, this.total - dep);
            if (dep <= 0)              this.paymentStatus = 'unpaid';
            else if (dep >= this.total) this.paymentStatus = 'paid';
            else                        this.paymentStatus = 'partial';
        },

        fmt(v) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(v || 0)) + ' ' + CURRENCY;
        },

        submitForm() {
            document.getElementById('customOrderForm').submit();
        },
    };
}
</script>

@endsection
