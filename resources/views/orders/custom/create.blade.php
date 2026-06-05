@extends('layouts.app')
@section('title', 'Nouvelle commande sur mesure')

@section('breadcrumb')
    <a href="{{ route('custom-orders.index') }}" class="hover:text-gray-600 transition-colors">Sur mesure</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Nouvelle commande</span>
@endsection

@section('content')

@php
$fabricsJson = $fabrics->map(function($f) {
    return [
        'id'              => $f->id,
        'name'            => $f->name,
        'reference'       => $f->reference,
        'price_per_meter' => $f->price_per_meter,
        'available_meters'=> $f->available_meters,
        'color'           => $f->color,
    ];
})->values();

$clientsJson = $clients->map(function($c) {
    return [
        'id'           => $c->id,
        'name'         => $c->full_name,
        'phone'        => $c->phone,
        'measurements' => $c->measurements->map(function($m) {
            return [
                'id'    => $m->id,
                'label' => $m->label ?: 'Mesures #' . $m->id,
                'is_default' => $m->is_default,
            ];
        })->values(),
    ];
})->values();
@endphp

<script>
const FABRICS  = {!! json_encode($fabricsJson) !!};
const CLIENTS  = {!! json_encode($clientsJson) !!};
const CURRENCY = '{{ env("CURRENCY", "FCFA") }}';
</script>

<form method="POST" action="{{ route('custom-orders.store') }}"
      enctype="multipart/form-data"
      x-data="customOrderForm()"
      x-on:submit.prevent="submitForm"
      id="customOrderForm">
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

            {{-- ── En-tête --}}
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-2xl bg-purple-50 flex items-center justify-center">
                    <i data-lucide="scissors" class="w-5 h-5 text-purple-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-display font-bold text-dark">Commande sur mesure</h2>
                    <p class="text-xs text-gray-400">Renseignez le modèle, le tissu et les mesures du client</p>
                </div>
            </div>

            {{-- ── 1. Client + Genre ──────────────────── --}}
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
                             })" class="relative">
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

            {{-- ── 2. Modèle ─────────────────────────── --}}
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
                                <option value="{{ $val }}" {{ old('garment_type') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Nom du modèle : liste prédéfinie + saisie libre --}}
                    <div x-data="{ showCustom: false, modelPreset: '{{ old('model_name', '') }}' }">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom du modèle</label>
                        <select x-show="!showCustom"
                                x-on:change="if($event.target.value==='__custom__'){showCustom=true;modelPreset='';}else{modelPreset=$event.target.value;}"
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark
                                       focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                            <option value="">— Sélectionner —</option>
                            @foreach(['Robe soirée','Costume 3 pièces','Boubou homme','Boubou femme','Ensemble enfant','Chemise slim','Pantalon taille haute','Robe de mariée','Costume traditionnel','Ensemble pagne','Robe africaine','Veste africaine'] as $preset)
                                <option value="{{ $preset }}">{{ $preset }}</option>
                            @endforeach
                            <option value="__custom__">✏ Nom personnalisé...</option>
                        </select>
                        <div x-show="showCustom" class="flex gap-2">
                            <input type="text" x-model="modelPreset"
                                   placeholder="Saisir un nom personnalisé..."
                                   class="flex-1 px-3 py-2 rounded-xl border border-purple-300 bg-purple-50 text-sm text-dark
                                          focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                            <button type="button" x-on:click="showCustom=false;modelPreset='';"
                                    class="px-2 rounded-xl border border-gray-200 text-gray-400 hover:text-red-500 hover:border-red-200 transition-colors">
                                <i data-lucide="x" style="width:14px;height:14px"></i>
                            </button>
                        </div>
                        <input type="hidden" name="model_name" x-model="modelPreset">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Description du modèle</label>
                    <textarea name="model_description" rows="3" placeholder="Détails de coupe, style, finitions souhaitées..."
                              class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark resize-none
                                     focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">{{ old('model_description') }}</textarea>
                </div>

                {{-- Photo du modèle avec prévisualisation --}}
                <div x-data="{ preview: null }">
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Photo du modèle (optionnel)</label>
                    <div class="flex items-start gap-4">
                        <div x-show="preview" class="w-28 h-28 rounded-xl border-2 border-purple-200 overflow-hidden shrink-0 relative">
                            <img :src="preview" class="w-full h-full object-cover">
                            <button type="button"
                                    x-on:click="preview=null; $refs.photoInput.value='';"
                                    class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs font-bold hover:bg-red-600">
                                ×
                            </button>
                        </div>
                        <label x-show="!preview"
                               class="flex-1 flex flex-col items-center justify-center h-28 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer
                                      hover:border-purple-400 hover:bg-purple-50 transition-all">
                            <i data-lucide="image-plus" class="w-7 h-7 text-gray-300 mb-1"></i>
                            <span class="text-xs text-gray-400 font-semibold">Cliquer pour choisir une photo</span>
                            <span class="text-xs text-gray-300 mt-0.5">JPG, PNG — max 2 Mo</span>
                            <input type="file" name="model_photo" accept="image/*" class="hidden" x-ref="photoInput"
                                   x-on:change="const f=$event.target.files[0]; if(f){const r=new FileReader();r.onload=e=>preview=e.target.result;r.readAsDataURL(f);}">
                        </label>
                        <div x-show="preview" class="flex-1 flex flex-col justify-center gap-2">
                            <p class="text-xs text-purple-600 font-semibold flex items-center gap-1">
                                <i data-lucide="check-circle" style="width:13px;height:13px"></i>
                                Photo sélectionnée
                            </p>
                            <button type="button" x-on:click="preview=null; $refs.photoInput.value='';"
                                    class="text-xs text-red-500 hover:underline text-left">Supprimer</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 3. Tissu ──────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4" x-data="{ clientOwnsFabric: false }"
                 x-on:change.capture="if($event.target.name==='client_owns_fabric') { clientOwnsFabric=$event.target.checked; if(clientOwnsFabric){ fabricId=''; fabricMeters=0; calcTotals(); } }">

                {{-- En-tête avec toggle --}}
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="layers" class="w-4 h-4 text-purple-600"></i>
                        Tissu
                    </h3>
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" name="client_owns_fabric" value="1"
                                   x-model="clientOwnsFabric"
                                   class="sr-only peer">
                            <div class="w-10 h-5 rounded-full transition-colors peer-checked:bg-purple-500 bg-gray-200"></div>
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                        </div>
                        <span class="text-xs font-semibold" :class="clientOwnsFabric ? 'text-purple-600' : 'text-gray-400'"
                              x-text="clientOwnsFabric ? 'Client apporte son tissu' : 'Tissu depuis le stock'"></span>
                    </label>
                </div>

                {{-- Contenu grisé si client apporte son tissu --}}
                <div :class="clientOwnsFabric ? 'opacity-40 pointer-events-none select-none' : ''"
                     class="space-y-4 transition-opacity duration-200">

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tissu en stock</label>
                            <div x-data="searchSelect({
                                    items: [{id:'',name:'— Aucun (client apporte son tissu) —',sub:''}].concat(FABRICS.map(f=>({id:f.id,name:f.name,sub:f.price_per_meter.toLocaleString('fr-FR')+' FCFA/m — '+f.available_meters+'m dispo'}))),
                                    labelKey: 'name',
                                    subKey: 'sub',
                                    placeholder: 'Rechercher un tissu...',
                                    inputName: 'fabric_product_id',
                                    onSelect: (id) => { fabricId = id; calcTotals(); }
                                 })" class="relative">
                                <input type="hidden" name="fabric_product_id" x-model="selectedId">
                                <button type="button" x-on:click="open = !open"
                                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm text-left
                                               focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                    <span :class="selectedId ? 'text-dark' : 'text-gray-400'" class="truncate"
                                          x-text="selectedId ? items.find(i=>i.id==selectedId)?.name : '— Aucun (client apporte son tissu) —'"></span>
                                    <i data-lucide="chevrons-up-down" style="width:14px;height:14px" class="text-gray-400 shrink-0 ml-2"></i>
                                </button>
                                <div x-show="open" x-on:click.outside="open=false" x-transition
                                     class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                                    <div class="p-2 border-b border-gray-100">
                                        <input type="text" x-model="search" x-on:input="filterItems"
                                               placeholder="Taper pour filtrer..."
                                               class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20">
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
                                        <li x-show="filtered.length === 0" class="px-3 py-4 text-center text-gray-400 text-sm">Aucun tissu</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Mètres nécessaires</label>
                            <input type="number" name="fabric_meters" x-model.number="fabricMeters"
                                   x-on:input="calcTotals"
                                   value="{{ old('fabric_meters') }}"
                                   min="0.5" step="0.5" placeholder="0.0"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right
                                          focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                        </div>
                    </div>

                    {{-- Info tissu sélectionné --}}
                    <div x-show="fabricId" x-transition class="flex items-center gap-4 bg-purple-50 rounded-xl px-4 py-3 text-xs">
                        <span class="text-gray-500">Stock :</span>
                        <span class="font-bold text-dark" x-text="selectedFabric ? selectedFabric.available_meters + ' m' : ''"></span>
                        <span class="text-gray-500 ml-2">Prix :</span>
                        <span class="font-bold text-dark" x-text="selectedFabric ? fmt(selectedFabric.price_per_meter) + '/m' : ''"></span>
                        <span class="text-gray-500 ml-2">Coût tissu :</span>
                        <span class="font-bold text-purple-700" x-text="fmt(fabricCost)"></span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Couleur / précision tissu</label>
                        <input type="text" name="fabric_color" value="{{ old('fabric_color') }}"
                               placeholder="Ex : Blanc cassé, rayé bleu marine..."
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark
                                      focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                    </div>
                </div>

                {{-- Message tissu client --}}
                <div x-show="clientOwnsFabric" x-transition
                     class="flex items-center gap-3 px-4 py-3 bg-purple-50 rounded-xl border border-purple-100">
                    <i data-lucide="info" class="w-4 h-4 text-purple-500 shrink-0"></i>
                    <p class="text-xs text-purple-700 font-medium">Le client apporte son propre tissu — aucun tissu du stock ne sera prélevé.</p>
                </div>
            </div>

            {{-- ── 4. Mesures ────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden" x-data="{ mode: 'existing', newLabel: '' }">
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="ruler" class="w-4 h-4 text-purple-600"></i>
                        Mesures
                    </h3>
                    <div x-show="clientId" class="flex items-center bg-gray-100 rounded-lg p-0.5 gap-0.5">
                        <button type="button" x-on:click="mode='existing'"
                                class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all"
                                :class="mode==='existing' ? 'bg-white text-dark shadow-sm' : 'text-gray-400 hover:text-gray-600'">
                            Fiche existante
                        </button>
                        <button type="button" x-on:click="mode='new'"
                                class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all"
                                :class="mode==='new' ? 'bg-white text-dark shadow-sm' : 'text-gray-400 hover:text-gray-600'">
                            Nouvelle fiche
                        </button>
                        <button type="button" x-on:click="mode='manual'"
                                class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all"
                                :class="mode==='manual' ? 'bg-white text-dark shadow-sm' : 'text-gray-400 hover:text-gray-600'">
                            Saisie directe
                        </button>
                    </div>
                </div>

                <div class="p-5 space-y-4">
                    {{-- Pas de client sélectionné --}}
                    <div x-show="!clientId" class="py-4 text-center text-gray-400 text-xs italic">
                        Sélectionnez un client pour accéder aux mesures.
                    </div>

                    {{-- Mode : Fiche existante --}}
                    <div x-show="clientId && mode==='existing'" x-transition>
                        <div x-show="clientMeasurements.length > 0" class="space-y-3">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Sélectionner une fiche</label>
                            <select name="measurement_id" x-model="measurementId"
                                    class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark
                                           focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                <option value="">— Choisir une fiche —</option>
                                <template x-for="m in clientMeasurements" :key="m.id">
                                    <option :value="m.id">
                                        <span x-text="m.label + (m.is_default ? ' (par défaut)' : '')"></span>
                                    </option>
                                </template>
                            </select>
                        </div>
                        <div x-show="clientMeasurements.length === 0"
                             class="py-4 text-center text-amber-700 bg-amber-50 rounded-xl text-xs font-medium border border-amber-100">
                            <i data-lucide="alert-triangle" class="w-4 h-4 mx-auto mb-1 text-amber-500"></i>
                            Aucune fiche pour ce client. Créez-en une ou saisissez directement.
                        </div>
                    </div>

                    {{-- Mode : Nouvelle fiche complète --}}
                    <div x-show="clientId && mode==='new'" x-transition class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom de la fiche <span class="text-red-400">*</span></label>
                            <input type="text" name="new_measurement_label" x-model="newLabel"
                                   placeholder="Ex : Mesures printemps 2026, Taille robe..."
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark
                                          focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                        </div>

                        {{-- ── Mesures FEMME ── --}}
                        <div x-show="gender === 'femme'" class="space-y-4">
                            {{-- Haut --}}
                            <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
                                Haut (robe, chemisier, veste)
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach([
                                    'f_longueur_epaule'       => 'Longueur épaule',
                                    'f_tour_poitrine'         => 'Tour de poitrine',
                                    'f_tour_taille'           => 'Tour de taille',
                                    'f_petites_hanches'       => 'Tour petites hanches',
                                    'f_grandes_hanches'       => 'Tour grandes hanches',
                                    'f_hauteur_saillant'      => 'Hauteur saillant',
                                    'f_ecart_saillants'       => 'Écart des saillants',
                                    'f_hauteur_buste_devant'  => 'Hauteur buste devant',
                                    'f_hauteur_buste_dos'     => 'Hauteur buste dos',
                                    'f_longueur_cote_buste'   => 'Longueur côté buste',
                                    'f_tour_manche'           => 'Tour de manche',
                                    'f_longueur_manche'       => 'Longueur de manche',
                                    'f_carrure_devant'        => 'Carrure devant',
                                    'f_carrure_dos'           => 'Carrure dos',
                                    'f_longueur_veste'        => 'Longueur veste',
                                    'f_tour_encolure'         => 'Tour encolure',
                                    'f_hauteur_fessier'       => 'Hauteur fessier',
                                ] as $field => $label)
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ $label }} <span class="text-gray-300">cm</span></label>
                                    <input type="number" name="new_measurement[{{ $field }}]"
                                           min="1" max="999" step="0.5" placeholder="—"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right
                                                  focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                </div>
                                @endforeach
                            </div>
                            {{-- Bas --}}
                            <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider flex items-center gap-1.5 mt-2">
                                <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
                                Bas (pantalon)
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach([
                                    'f_tour_ceinture'         => 'Tour de ceinture',
                                    'f_tour_bassin'           => 'Tour de bassin',
                                    'f_hauteur_bassin'        => 'Hauteur bassin',
                                    'f_longueur_assise'       => "Longueur d'assise",
                                    'f_fourche_devant'        => 'Fourche devant',
                                    'f_fourche_dos'           => 'Fourche dos',
                                    'f_entrejambe'            => 'Entrejambe',
                                    'f_longueur_pantalon'     => 'Longueur pantalon',
                                    'f_tour_cuisse'           => 'Tour de cuisse',
                                    'f_largeur_bas'           => 'Largeur du bas',
                                ] as $field => $label)
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ $label }} <span class="text-gray-300">cm</span></label>
                                    <input type="number" name="new_measurement[{{ $field }}]"
                                           min="1" max="999" step="0.5" placeholder="—"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right
                                                  focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                </div>
                                @endforeach
                            </div>
                            {{-- Longueurs robes & jupes --}}
                            <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider flex items-center gap-1.5 mt-2">
                                <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
                                Longueurs robes &amp; jupes
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach([
                                    'f_robe_longue'           => 'Robe longue',
                                    'f_robe_avant_genoux'     => 'Robe avant genoux',
                                    'f_robe_apres_genoux'     => 'Robe après genoux',
                                    'f_robe_trois_quarts'     => 'Robe trois quarts',
                                    'f_jupe_longue'           => 'Jupe longue',
                                    'f_jupe_genoux'           => 'Jupe genoux',
                                    'f_jupe_trois_quarts'     => 'Jupe trois quarts',
                                ] as $field => $label)
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ $label }} <span class="text-gray-300">cm</span></label>
                                    <input type="number" name="new_measurement[{{ $field }}]"
                                           min="1" max="999" step="0.5" placeholder="—"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right
                                                  focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- ── Mesures HOMME ── --}}
                        <div x-show="gender === 'homme'" class="space-y-4">
                            {{-- Haut --}}
                            <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
                                Haut (chemise, veste, boubou)
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach([
                                    'h_epaule'                => 'Épaule',
                                    'h_manche_longue'         => 'Manche longue',
                                    'h_manche_courte'         => 'Manche courte',
                                    'h_tour_poitrine'         => 'Tour de poitrine',
                                    'h_tour_taille_ventre'    => 'Tour taille/ventre',
                                    'h_carrure_dos'           => 'Carrure dos',
                                    'h_longueur_chemise'      => 'Longueur chemise',
                                    'h_longueur_veste'        => 'Longueur veste',
                                    'h_contour_manche'        => 'Contour manche',
                                    'h_tour_col'              => 'Tour de col',
                                    'h_longueur_devant'       => 'Longueur devant',
                                ] as $field => $label)
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ $label }} <span class="text-gray-300">cm</span></label>
                                    <input type="number" name="new_measurement[{{ $field }}]"
                                           min="1" max="999" step="0.5" placeholder="—"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right
                                                  focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                </div>
                                @endforeach
                            </div>
                            {{-- Bas --}}
                            <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider flex items-center gap-1.5 mt-2">
                                <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
                                Bas (pantalon)
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach([
                                    'h_tour_ceinture'         => 'Tour de ceinture',
                                    'h_tour_bassin'           => 'Tour de bassin',
                                    'h_tour_cuisse'           => 'Tour de cuisse',
                                    'h_largeur_genoux'        => 'Largeur genoux',
                                    'h_tour_mollet'           => 'Tour de mollet',
                                    'h_diametre_bas'          => 'Diamètre du bas',
                                    'h_longueur_pantalon'     => 'Longueur pantalon',
                                    'h_longueur_culotte'      => 'Longueur culotte',
                                    'h_pisset'                => 'Pisset (braguette)',
                                ] as $field => $label)
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ $label }} <span class="text-gray-300">cm</span></label>
                                    <input type="number" name="new_measurement[{{ $field }}]"
                                           min="1" max="999" step="0.5" placeholder="—"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right
                                                  focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- ── Mesures ENFANT (générique) ── --}}
                        <div x-show="gender === 'enfant'" class="space-y-4">
                            <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
                                Mesures enfant
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach([
                                    'e_tour_poitrine'         => 'Tour de poitrine',
                                    'e_tour_taille'           => 'Tour de taille',
                                    'e_tour_hanches'          => 'Tour de hanches',
                                    'e_epaule'                => 'Épaule',
                                    'e_longueur_manche'       => 'Longueur manche',
                                    'e_longueur_veste'        => 'Longueur veste/robe',
                                    'e_tour_ceinture'         => 'Tour de ceinture',
                                    'e_longueur_pantalon'     => 'Longueur pantalon',
                                    'e_entrejambe'            => 'Entrejambe',
                                    'e_tour_cuisse'           => 'Tour de cuisse',
                                ] as $field => $label)
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ $label }} <span class="text-gray-300">cm</span></label>
                                    <input type="number" name="new_measurement[{{ $field }}]"
                                           min="1" max="999" step="0.5" placeholder="—"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right
                                                  focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center gap-2 px-3 py-2 bg-purple-50 rounded-xl text-xs text-purple-700">
                            <input type="checkbox" name="new_measurement_default" id="meas_default" value="1" class="rounded">
                            <label for="meas_default" class="font-semibold cursor-pointer">Définir comme fiche par défaut pour ce client</label>
                        </div>
                        <p class="text-xs text-gray-400">La fiche sera créée et associée au client lors de la sauvegarde.</p>
                    </div>

                    {{-- Mode : Saisie directe (notes libres) --}}
                    <div x-show="clientId && mode==='manual'" x-transition class="space-y-4">
                        <p class="text-xs text-gray-500">Les mesures seront enregistrées avec la commande pour l'atelier.</p>

                        {{-- ── Mesures FEMME directe ── --}}
                        <div x-show="gender === 'femme'" class="space-y-4">
                            <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
                                Haut (robe, chemisier, veste)
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach([
                                    'f_longueur_epaule'       => 'Longueur épaule',
                                    'f_tour_poitrine'         => 'Tour de poitrine',
                                    'f_tour_taille'           => 'Tour de taille',
                                    'f_petites_hanches'       => 'Tour petites hanches',
                                    'f_grandes_hanches'       => 'Tour grandes hanches',
                                    'f_hauteur_saillant'      => 'Hauteur saillant',
                                    'f_ecart_saillants'       => 'Écart des saillants',
                                    'f_hauteur_buste_devant'  => 'Hauteur buste devant',
                                    'f_hauteur_buste_dos'     => 'Hauteur buste dos',
                                    'f_longueur_cote_buste'   => 'Longueur côté buste',
                                    'f_tour_manche'           => 'Tour de manche',
                                    'f_longueur_manche'       => 'Longueur de manche',
                                    'f_carrure_devant'        => 'Carrure devant',
                                    'f_carrure_dos'           => 'Carrure dos',
                                    'f_longueur_veste'        => 'Longueur veste',
                                    'f_tour_encolure'         => 'Tour encolure',
                                    'f_hauteur_fessier'       => 'Hauteur fessier',
                                ] as $field => $label)
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ $label }} <span class="text-gray-300">cm</span></label>
                                    <input type="number" name="manual_measurement[{{ $field }}]"
                                           min="1" max="999" step="0.5" placeholder="—"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right
                                                  focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                </div>
                                @endforeach
                            </div>
                            <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
                                Bas (pantalon)
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach([
                                    'f_tour_ceinture'         => 'Tour de ceinture',
                                    'f_tour_bassin'           => 'Tour de bassin',
                                    'f_hauteur_bassin'        => 'Hauteur bassin',
                                    'f_longueur_assise'       => "Longueur d'assise",
                                    'f_fourche_devant'        => 'Fourche devant',
                                    'f_fourche_dos'           => 'Fourche dos',
                                    'f_entrejambe'            => 'Entrejambe',
                                    'f_longueur_pantalon'     => 'Longueur pantalon',
                                    'f_tour_cuisse'           => 'Tour de cuisse',
                                    'f_largeur_bas'           => 'Largeur du bas',
                                ] as $field => $label)
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ $label }} <span class="text-gray-300">cm</span></label>
                                    <input type="number" name="manual_measurement[{{ $field }}]"
                                           min="1" max="999" step="0.5" placeholder="—"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right
                                                  focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                </div>
                                @endforeach
                            </div>
                            <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
                                Longueurs robes &amp; jupes
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach([
                                    'f_robe_longue'           => 'Robe longue',
                                    'f_robe_avant_genoux'     => 'Robe avant genoux',
                                    'f_robe_apres_genoux'     => 'Robe après genoux',
                                    'f_robe_trois_quarts'     => 'Robe trois quarts',
                                    'f_jupe_longue'           => 'Jupe longue',
                                    'f_jupe_genoux'           => 'Jupe genoux',
                                    'f_jupe_trois_quarts'     => 'Jupe trois quarts',
                                ] as $field => $label)
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ $label }} <span class="text-gray-300">cm</span></label>
                                    <input type="number" name="manual_measurement[{{ $field }}]"
                                           min="1" max="999" step="0.5" placeholder="—"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right
                                                  focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- ── Mesures HOMME directe ── --}}
                        <div x-show="gender === 'homme'" class="space-y-4">
                            <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
                                Haut (chemise, veste, boubou)
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach([
                                    'h_epaule'                => 'Épaule',
                                    'h_manche_longue'         => 'Manche longue',
                                    'h_manche_courte'         => 'Manche courte',
                                    'h_tour_poitrine'         => 'Tour de poitrine',
                                    'h_tour_taille_ventre'    => 'Tour taille/ventre',
                                    'h_carrure_dos'           => 'Carrure dos',
                                    'h_longueur_chemise'      => 'Longueur chemise',
                                    'h_longueur_veste'        => 'Longueur veste',
                                    'h_contour_manche'        => 'Contour manche',
                                    'h_tour_col'              => 'Tour de col',
                                    'h_longueur_devant'       => 'Longueur devant',
                                ] as $field => $label)
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ $label }} <span class="text-gray-300">cm</span></label>
                                    <input type="number" name="manual_measurement[{{ $field }}]"
                                           min="1" max="999" step="0.5" placeholder="—"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right
                                                  focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                </div>
                                @endforeach
                            </div>
                            <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider flex items-center gap-1.5 mt-2">
                                <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
                                Bas (pantalon)
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach([
                                    'h_tour_ceinture'         => 'Tour de ceinture',
                                    'h_tour_bassin'           => 'Tour de bassin',
                                    'h_tour_cuisse'           => 'Tour de cuisse',
                                    'h_largeur_genoux'        => 'Largeur genoux',
                                    'h_tour_mollet'           => 'Tour de mollet',
                                    'h_diametre_bas'          => 'Diamètre du bas',
                                    'h_longueur_pantalon'     => 'Longueur pantalon',
                                    'h_longueur_culotte'      => 'Longueur culotte',
                                    'h_pisset'                => 'Pisset (braguette)',
                                ] as $field => $label)
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ $label }} <span class="text-gray-300">cm</span></label>
                                    <input type="number" name="manual_measurement[{{ $field }}]"
                                           min="1" max="999" step="0.5" placeholder="—"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right
                                                  focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- ── Mesures ENFANT directe ── --}}
                        <div x-show="gender === 'enfant'" class="space-y-4">
                            <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
                                Mesures enfant
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach([
                                    'e_tour_poitrine'         => 'Tour de poitrine',
                                    'e_tour_taille'           => 'Tour de taille',
                                    'e_tour_hanches'          => 'Tour de hanches',
                                    'e_epaule'                => 'Épaule',
                                    'e_longueur_manche'       => 'Longueur manche',
                                    'e_longueur_veste'        => 'Longueur veste/robe',
                                    'e_tour_ceinture'         => 'Tour de ceinture',
                                    'e_longueur_pantalon'     => 'Longueur pantalon',
                                    'e_entrejambe'            => 'Entrejambe',
                                    'e_tour_cuisse'           => 'Tour de cuisse',
                                ] as $field => $label)
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ $label }} <span class="text-gray-300">cm</span></label>
                                    <input type="number" name="manual_measurement[{{ $field }}]"
                                           min="1" max="999" step="0.5" placeholder="—"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark text-right
                                                  focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 5. Accessoires ────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="tag" class="w-4 h-4 text-purple-600"></i>
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
                    Boutons, fermetures, broderies, etc. — Ajoutez les accessoires inclus dans la commande.
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

            {{-- ── 6. Production ─────────────────────── --}}
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
                                <option value="{{ $couturier->id }}" {{ old('assigned_to') == $couturier->id ? 'selected' : '' }}>
                                    {{ $couturier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Date de livraison prévue</label>
                        <input type="date" name="delivery_date" value="{{ old('delivery_date') }}"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark
                                      focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Notes internes</label>
                    <textarea name="notes" rows="2" placeholder="Instructions pour l'atelier, priorité, spécifications techniques..."
                              class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark resize-none
                                     focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">{{ old('notes') }}</textarea>
                </div>
            </div>

        </div>

        {{-- ══════════ COLONNE DROITE ══════════ --}}
        <div class="space-y-5">

            {{-- ── Récapitulatif coûts ──────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden sticky top-6">
                <div class="px-5 py-4 border-b border-gray-50">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-purple-600"></i>
                        Récapitulatif
                    </h3>
                </div>
                <div class="p-5 space-y-4">

                    {{-- Lignes coûts --}}
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Tissu</span>
                            <span class="font-semibold text-dark" x-text="fmt(fabricCost)"></span>
                        </div>

                        {{-- Main d'œuvre --}}
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span>Main d'œuvre <span class="text-red-400">*</span></span>
                            <div class="flex items-center gap-1">
                                <input type="number" name="labor_cost" x-model.number="laborCost"
                                       x-on:input="calcTotals"
                                       value="{{ old('labor_cost', 0) }}"
                                       min="0" step="500" placeholder="0" required
                                       class="w-28 px-2 py-1 rounded-lg border border-gray-200 text-xs text-right
                                              focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                                <span class="text-xs text-gray-400" x-text="CURRENCY"></span>
                            </div>
                        </div>

                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Accessoires</span>
                            <span class="font-semibold text-dark" x-text="fmt(accessoriesCost)"></span>
                        </div>

                        <div class="border-t border-gray-100 pt-2 flex justify-between">
                            <span class="text-sm font-display font-bold text-dark">TOTAL</span>
                            <span class="text-xl font-display font-bold text-purple-600" x-text="fmt(total)"></span>
                        </div>
                    </div>

                    {{-- Paiement --}}
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
                                       value="{{ old('deposit', 0) }}"
                                       min="0" step="500" placeholder="0"
                                       class="w-full px-3 py-2.5 pr-16 rounded-xl border border-gray-200 text-sm font-bold text-dark text-right
                                              focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400 pointer-events-none"
                                      x-text="CURRENCY"></span>
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

                    {{-- Boutons --}}
                    <div class="space-y-2 pt-2 border-t border-gray-50">
                        <button type="submit" :disabled="!isValid"
                                class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-purple-600 text-white
                                       text-sm font-bold hover:bg-purple-700 active:scale-95 transition-all shadow-sm shadow-purple-500/20
                                       disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="check-circle" style="width:16px;height:16px"></i>
                            Créer la commande
                        </button>
                        <a href="{{ route('custom-orders.index') }}"
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

/* ── Composant searchSelect réutilisable ─────────────── */
function searchSelect({ items, labelKey, subKey, placeholder, inputName, onSelect }) {
    return {
        items,
        labelKey,
        subKey,
        placeholder,
        inputName,
        onSelect,
        open: false,
        search: ''  ,
        selectedId: '',
        filtered: [],
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
                (i[this.subKey] || '').toLowerCase().includes(q)
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
        clientId:       '',
        gender:         'femme',
        fabricId:       '',
        fabricMeters:   0,
        laborCost:      0,
        deposit:        0,
        paymentMethod:  'cash',
        measurementId:  '',
        accessories:    [],
        accCounter:     0,

        // Computed
        fabricCost:      0,
        accessoriesCost: 0,
        total:           0,
        balance:         0,
        paymentStatus:   'unpaid',

        get clientMeasurements() {
            const c = CLIENTS.find(c => c.id == this.clientId);
            return c ? c.measurements : [];
        },

        get selectedFabric() {
            return FABRICS.find(f => f.id == this.fabricId) || null;
        },

        get isValid() {
            return this.clientId && this.gender && document.querySelector('[name=garment_type]')?.value;
        },

        init() {
            this.$watch('fabricId', () => this.calcTotals());
            this.$watch('fabricMeters', () => this.calcTotals());
            this.$watch('laborCost', () => this.calcTotals());
        },

        onClientChange() {
            this.measurementId = '';
            const c = CLIENTS.find(c => c.id == this.clientId);
            if (c) {
                const def = c.measurements.find(m => m.is_default);
                if (def) this.measurementId = def.id;
            }
        },

        onFabricChange() {
            this.calcTotals();
        },

        addAccessory() {
            this.accessories.push({ id: ++this.accCounter, name: '', qty: 1, price: 0 });
            this.$nextTick(() => lucide.createIcons());
        },

        removeAccessory(index) {
            this.accessories.splice(index, 1);
            this.calcTotals();
        },

        calcTotals() {
            // Coût tissu
            if (this.fabricId && this.fabricMeters > 0 && this.selectedFabric) {
                this.fabricCost = this.selectedFabric.price_per_meter * this.fabricMeters;
            } else {
                this.fabricCost = 0;
            }

            // Coût accessoires
            this.accessoriesCost = this.accessories.reduce((s, a) => s + ((a.price || 0) * (a.qty || 1)), 0);

            // Total
            this.total = this.fabricCost + (this.laborCost || 0) + this.accessoriesCost;
            this.calcChange();
        },

        calcChange() {
            const dep = this.deposit || 0;
            this.balance = Math.max(0, this.total - dep);
            if (dep <= 0)             this.paymentStatus = 'unpaid';
            else if (dep >= this.total) this.paymentStatus = 'paid';
            else                        this.paymentStatus = 'partial';
        },

        fmt(v) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(v || 0)) + ' ' + CURRENCY;
        },

        submitForm() {
            if (!this.isValid) return;
            document.getElementById('customOrderForm').submit();
        },
    };
}
</script>

@endsection
