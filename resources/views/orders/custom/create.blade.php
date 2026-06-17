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
    <input type="hidden" name="is_group_order" :value="isGroup ? '1' : '0'">

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
                    <h2 class="text-lg font-display font-bold text-dark" x-text="isGroup ? 'Commande groupe sur mesure' : 'Commande sur mesure'"></h2>
                    <p class="text-xs text-gray-400" x-text="isGroup ? 'Famille, groupe d\'artistes — plusieurs personnes, plusieurs tenues' : 'Renseignez le modèle, le tissu et les mesures du client'"></p>
                </div>
            </div>

            {{-- ── 0. TYPE DE COMMANDE ──────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2 mb-4">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-purple-600"></i>
                    Type de commande
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" x-on:click="isGroup = false; $nextTick(() => lucide.createIcons())"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 transition-all text-left"
                            :class="!isGroup ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300'">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                             :class="!isGroup ? 'bg-purple-100' : 'bg-gray-100'">
                            <i data-lucide="user" class="w-4 h-4" :class="!isGroup ? 'text-purple-600' : 'text-gray-400'"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold" :class="!isGroup ? 'text-purple-700' : 'text-gray-600'">Individuelle</p>
                            <p class="text-xs text-gray-400">Pour une seule personne</p>
                        </div>
                    </button>
                    <button type="button" x-on:click="isGroup = true; $nextTick(() => lucide.createIcons())"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 transition-all text-left"
                            :class="isGroup ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300'">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                             :class="isGroup ? 'bg-purple-100' : 'bg-gray-100'">
                            <i data-lucide="users" class="w-4 h-4" :class="isGroup ? 'text-purple-600' : 'text-gray-400'"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold" :class="isGroup ? 'text-purple-700' : 'text-gray-600'">Groupe</p>
                            <p class="text-xs text-gray-400">Famille, groupe d'artistes…</p>
                        </div>
                    </button>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 BLOC GROUPE
                 ════════════════════════════════════════════════════════════ --}}
            <template x-if="isGroup">
                <div class="space-y-5">

                    {{-- Infos groupe --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
                        <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                            <i data-lucide="info" class="w-4 h-4 text-purple-600"></i>
                            Informations du groupe
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Client / Responsable <span class="text-red-400">*</span></label>
                                <div x-data="searchSelect({
                                        items: CLIENTS,
                                        labelKey: 'name',
                                        subKey: 'phone',
                                        placeholder: 'Rechercher un client...',
                                        inputName: 'client_id',
                                        onSelect: (id) => { clientId = id; }
                                     })" class="relative">
                                    <input type="hidden" name="client_id" x-model="selectedId">
                                    <button type="button" x-on:click="open = !open"
                                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm text-left focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                        <span :class="selectedId ? 'text-dark' : 'text-gray-400'"
                                              x-text="selectedId ? (items.find(i=>i.id==selectedId)?.name + ' — ' + items.find(i=>i.id==selectedId)?.phone) : placeholder"></span>
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
                                                <li x-on:click="select(item)"
                                                    class="flex items-center gap-3 px-3 py-2.5 hover:bg-purple-50 cursor-pointer"
                                                    :class="selectedId == item.id ? 'bg-purple-50' : ''">
                                                    <div class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-xs shrink-0"
                                                         x-text="item.name.charAt(0).toUpperCase()"></div>
                                                    <div>
                                                        <p class="text-sm font-semibold" x-text="item.name"></p>
                                                        <p class="text-xs text-gray-400" x-text="item.phone"></p>
                                                    </div>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom du groupe / référence <span class="text-red-400">*</span></label>
                                <input type="text" name="group_name" placeholder="Ex : Tenues mariage Koné, Groupe Les Étoiles…"
                                       class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Occasion / événement</label>
                                <select name="group_occasion"
                                        class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                                    <option value="">— Choisir —</option>
                                    <option>Mariage</option>
                                    <option>Baptême</option>
                                    <option>Concert / Spectacle</option>
                                    <option>Cérémonie</option>
                                    <option>Uniforme / Tenue commune</option>
                                    <option>Autre</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Date de livraison prévue</label>
                                <input type="date" name="delivery_date"
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                       class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                            </div>
                        </div>
                    </div>

                    {{-- Composition du groupe --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
                        <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-purple-600"></i>
                            Composition du groupe
                            <span class="ml-auto text-xs font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full"
                                  x-text="groupTotalPersons + ' personne' + (groupTotalPersons > 1 ? 's' : '')"></span>
                        </h3>

                        {{-- Compteurs --}}
                        <div class="grid grid-cols-3 gap-3">
                            <template x-for="cat in groupCategories" :key="cat.key">
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <p class="text-xs font-semibold text-gray-600 mb-0.5" x-text="cat.label"></p>
                                    <p class="text-xs text-gray-400 mb-3" x-text="cat.sub"></p>
                                    <div class="flex items-center justify-between">
                                        <button type="button" x-on:click="changeCount(cat.key, -1)"
                                                class="w-7 h-7 rounded-lg border border-gray-200 bg-white text-purple-600 font-bold text-lg flex items-center justify-center hover:bg-purple-50 transition-colors">−</button>
                                        <span class="text-lg font-bold text-dark" x-text="groupCounts[cat.key]"></span>
                                        <button type="button" x-on:click="changeCount(cat.key, 1)"
                                                class="w-7 h-7 rounded-lg border border-gray-200 bg-white text-purple-600 font-bold text-lg flex items-center justify-center hover:bg-purple-50 transition-colors">+</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Onglets membres --}}
                        <div class="flex flex-wrap gap-2" x-show="groupTotalPersons > 0">
                            <template x-for="m in groupMembers" :key="m.id">
                                <button type="button"
                                        x-on:click="activeGroupMember = m.id; $nextTick(() => lucide.createIcons())"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-semibold transition-all"
                                        :class="activeGroupMember === m.id
                                            ? 'border-purple-500 bg-purple-600 text-white'
                                            : 'border-gray-200 text-gray-600 hover:border-purple-300'">
                                    <span class="w-2 h-2 rounded-full inline-block"
                                          :class="m.type === 'homme' ? 'bg-blue-400' : m.type === 'femme' ? 'bg-pink-400' : 'bg-amber-400'"></span>
                                    <span x-text="m.nom || m.label"></span>
                                </button>
                            </template>
                        </div>

                        {{-- Panneau du membre actif --}}
                        <template x-if="activeMember">
                            <div class="border border-purple-100 rounded-xl p-4 bg-purple-50/40 space-y-4">

                                {{-- En-tête membre --}}
                                <div class="flex items-center gap-3">
                                    <span class="text-xl"
                                          x-text="activeMember.type === 'homme' ? '🧔' : activeMember.type === 'femme' ? '👩' : '🧒'"></span>
                                    <span class="text-xs font-bold px-2 py-1 rounded-full"
                                          :class="activeMember.type === 'homme' ? 'bg-blue-100 text-blue-700' : activeMember.type === 'femme' ? 'bg-pink-100 text-pink-700' : 'bg-amber-100 text-amber-700'"
                                          x-text="activeMember.label"></span>
                                </div>

                                {{-- Note contextuelle selon le type --}}
                                <div class="text-xs px-3 py-2 rounded-lg font-medium"
                                     :class="activeMember.type === 'homme' ? 'bg-blue-50 text-blue-700 border border-blue-100' : activeMember.type === 'femme' ? 'bg-pink-50 text-pink-700 border border-pink-100' : 'bg-amber-50 text-amber-700 border border-amber-100'">
                                    <template x-if="activeMember.type === 'homme'">
                                        <span>🧔 <strong>Sur mesure homme</strong> — Col et manches essentiels pour chemises et vestes.</span>
                                    </template>
                                    <template x-if="activeMember.type === 'femme'">
                                        <span>👩 <strong>Sur mesure femme</strong> — Poitrine et taille obligatoires. Précisez la longueur souhaitée.</span>
                                    </template>
                                    <template x-if="activeMember.type === 'enfant'">
                                        <span>🧒 <strong>Sur mesure enfant</strong> — Âge et taille corporelle aident à l'ajustement.</span>
                                    </template>
                                </div>

                                {{-- Infos personnelles enfant --}}
                                <template x-if="activeMember.type === 'enfant'">
                                    <div>
                                        <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                            <i data-lucide="user" style="width:12px;height:12px"></i>
                                            Informations
                                        </p>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">Prénom <span class="text-red-400">*</span></label>
                                                <input type="text" x-model="activeMember.prenom" placeholder="Prénom de l'enfant"
                                                       class="w-full px-2 py-1.5 rounded-lg border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">Âge (ans)</label>
                                                <input type="number" x-model="activeMember.age" placeholder="Ex: 7" min="0" max="17"
                                                       class="w-full px-2 py-1.5 rounded-lg border border-gray-200 bg-white text-sm text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">Taille corporelle (cm)</label>
                                                <input type="number" x-model="activeMember.taille_corp" placeholder="Ex: 120"
                                                       class="w-full px-2 py-1.5 rounded-lg border border-gray-200 bg-white text-sm text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Infos personnelles homme/femme --}}
                                <template x-if="activeMember.type !== 'enfant'">
                                    <div>
                                        <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                            <i data-lucide="user" style="width:12px;height:12px"></i>
                                            Informations personnelles
                                        </p>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">Prénom <span class="text-red-400">*</span></label>
                                                <input type="text" x-model="activeMember.prenom" placeholder="Prénom"
                                                       class="w-full px-2 py-1.5 rounded-lg border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">Téléphone</label>
                                                <input type="text" x-model="activeMember.tel" placeholder="06 xxx xxx"
                                                       class="w-full px-2 py-1.5 rounded-lg border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">Pointure chaussure</label>
                                                <input type="number" x-model="activeMember.pointure" placeholder="Ex: 42"
                                                       class="w-full px-2 py-1.5 rounded-lg border border-gray-200 bg-white text-sm text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Mesures --}}
                                <div>
                                    <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                        <i data-lucide="ruler" style="width:12px;height:12px"></i>
                                        Mesures (cm)
                                    </p>
                                    {{-- FEMME --}}
                                    <template x-if="activeMember.type === 'femme'">
                                        <div class="space-y-3">
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Haut</p>
                                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                                <template x-for="f in mesuresFemmeHaut" :key="f.key">
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-1" x-text="f.label + ' cm'"></label>
                                                        <input type="number" min="1" max="999" step="0.5" placeholder="—"
                                                               x-model="activeMember.measurements[f.key]"
                                                               class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                                    </div>
                                                </template>
                                            </div>
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-2">Bas</p>
                                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                                <template x-for="f in mesuresFemmeBas" :key="f.key">
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-1" x-text="f.label + ' cm'"></label>
                                                        <input type="number" min="1" max="999" step="0.5" placeholder="—"
                                                               x-model="activeMember.measurements[f.key]"
                                                               class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    {{-- HOMME --}}
                                    <template x-if="activeMember.type === 'homme'">
                                        <div class="space-y-3">
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Haut</p>
                                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                                <template x-for="f in mesuresHommeHaut" :key="f.key">
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-1" x-text="f.label + ' cm'"></label>
                                                        <input type="number" min="1" max="999" step="0.5" placeholder="—"
                                                               x-model="activeMember.measurements[f.key]"
                                                               class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                                    </div>
                                                </template>
                                            </div>
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-2">Bas</p>
                                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                                <template x-for="f in mesuresHommeBas" :key="f.key">
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-1" x-text="f.label + ' cm'"></label>
                                                        <input type="number" min="1" max="999" step="0.5" placeholder="—"
                                                               x-model="activeMember.measurements[f.key]"
                                                               class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    {{-- ENFANT --}}
                                    <template x-if="activeMember.type === 'enfant'">
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                            <template x-for="f in mesuresEnfant" :key="f.key">
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1" x-text="f.label + ' cm'"></label>
                                                    <input type="number" min="1" max="999" step="0.5" placeholder="—"
                                                           x-model="activeMember.measurements[f.key]"
                                                           class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                {{-- Vêtements du membre --}}
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider flex items-center gap-1.5">
                                            <i data-lucide="shirt" style="width:12px;height:12px"></i>
                                            Vêtement(s)
                                        </p>
                                        <button type="button"
                                                x-on:click="activeMember.garments.push({garment_type:'',model_name:'',model_description:'',labor_cost:0,qty:1}); $nextTick(()=>lucide.createIcons()); calcTotals()"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-purple-100 text-purple-700 text-xs font-semibold hover:bg-purple-200 transition-colors">
                                            <i data-lucide="plus" style="width:11px;height:11px"></i>
                                            Ajouter
                                        </button>
                                    </div>
                                    <div class="space-y-3">
                                        <template x-for="(g, gi) in activeMember.garments" :key="gi">
                                            <div class="bg-white border border-gray-200 rounded-xl p-3 relative">
                                                <button type="button" x-show="activeMember.garments.length > 1"
                                                        x-on:click="activeMember.garments.splice(gi,1); calcTotals()"
                                                        class="absolute top-2 right-2 w-5 h-5 rounded-full bg-red-50 text-red-400 hover:bg-red-100 flex items-center justify-center text-xs font-bold">×</button>
                                                <div class="grid grid-cols-2 gap-3 mb-2">
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-1">Type <span class="text-red-400">*</span></label>
                                                        <select x-model="g.garment_type"
                                                                class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                                            <option value="">— Choisir —</option>
                                                            @foreach($garmentTypes as $val => $lbl)
                                                                <option value="{{ $val }}">{{ $lbl }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-1">Modèle</label>
                                                        <select x-model="g.model_name"
                                                                class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                                            <option value="">— Sélectionner —</option>
                                                            @foreach(['Robe soirée','Costume 3 pièces','Boubou homme','Boubou femme','Ensemble enfant','Chemise slim','Pantalon taille haute','Robe de mariée','Costume traditionnel','Ensemble pagne','Robe africaine','Veste africaine'] as $preset)
                                                                <option value="{{ $preset }}">{{ $preset }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="block text-xs text-gray-500 mb-1">Description / coupe</label>
                                                    <textarea x-model="g.model_description" rows="2" placeholder="Détails style, finitions…"
                                                              class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-purple-500/20"></textarea>
                                                </div>
                                                <div class="grid grid-cols-3 gap-3">
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-1">Main d'œuvre (FCFA)</label>
                                                        <input type="number" x-model.number="g.labor_cost" min="0" step="500" placeholder="0"
                                                               x-on:input="calcTotals()"
                                                               class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-1">Quantité</label>
                                                        <input type="number" x-model.number="g.qty" min="1" placeholder="1"
                                                               x-on:input="calcTotals()"
                                                               class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-1">Urgence</label>
                                                        <select x-model="g.urgence"
                                                                class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                                                            <option value="">— Normal —</option>
                                                            <option value="urgent">⚡ Urgent</option>
                                                            <option value="tres_urgent">🔴 Très urgent</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Notes spéciales du membre --}}
                                <div>
                                    <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                        <i data-lucide="file-text" style="width:12px;height:12px"></i>
                                        Notes spéciales / particularités
                                    </p>
                                    <textarea x-model="activeMember.notes_speciales" rows="2"
                                              placeholder="Ex: épaules asymétriques, taille plus petite, préfère coupes amples, allergie certaines matières…"
                                              class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm resize-none focus:outline-none focus:ring-2 focus:ring-purple-500/20"></textarea>
                                </div>
                            </div>
                        </template>

                        {{-- Champ caché JSON pour tous les membres --}}
                        <input type="hidden" name="group_members" :value="JSON.stringify(groupMembers)">
                    </div>

                    {{-- Photos multiples du modèle --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
                        <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                            <i data-lucide="image" class="w-4 h-4 text-purple-600"></i>
                            Photos du modèle
                            <span class="text-xs text-gray-400 font-normal">(jusqu'à 8, JPG/PNG, max 5 Mo chacune)</span>
                        </h3>
                        <div class="grid grid-cols-4 gap-3" x-data="{ slots: Array.from({length:8},(_,i)=>({idx:i,preview:null})) }">
                            <template x-for="slot in slots" :key="slot.idx">
                                <label class="aspect-square rounded-xl border-2 border-dashed cursor-pointer transition-all relative overflow-hidden flex flex-col items-center justify-center"
                                       :class="slot.preview ? 'border-purple-300' : 'border-gray-200 hover:border-purple-300 hover:bg-purple-50'">
                                    <img x-show="slot.preview" :src="slot.preview" class="absolute inset-0 w-full h-full object-cover rounded-xl">
                                    <div x-show="!slot.preview" class="flex flex-col items-center gap-1">
                                        <i data-lucide="image-plus" class="w-6 h-6 text-gray-300"></i>
                                        <span class="text-xs text-gray-300" x-text="'Photo ' + (slot.idx + 1)"></span>
                                    </div>
                                    <button type="button" x-show="slot.preview"
                                            x-on:click.prevent="slot.preview = null"
                                            class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center z-10 hover:bg-red-600">×</button>
                                    <input type="file" name="model_photos[]" accept="image/*" class="hidden"
                                           x-on:change="const f=$event.target.files[0]; if(f){const r=new FileReader();r.onload=e=>slot.preview=e.target.result;r.readAsDataURL(f);}">
                                </label>
                            </template>
                        </div>
                    </div>

                    {{-- Description globale --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-5">
                        <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2 mb-4">
                            <i data-lucide="file-text" class="w-4 h-4 text-purple-600"></i>
                            Description / instructions générales
                        </h3>
                        <textarea name="model_description" rows="3" placeholder="Style commun, finitions souhaitées pour l'ensemble du groupe…"
                                  class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark resize-none focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all"></textarea>
                    </div>
                </div>
            </template>

            {{-- ════════════════════════════════════════════════════════════
                 BLOC INDIVIDUEL (comportement original, masqué si groupe)
                 ════════════════════════════════════════════════════════════ --}}
            <template x-if="!isGroup">
                <div class="space-y-5">

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
                            <select name="measurement_id" x-model="measurementId" :disabled="mode !== 'existing'"
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

                </div>{{-- fin espace-y-5 individuel --}}
            </template>{{-- fin x-if !isGroup --}}

        </div>{{-- fin lg:col-span-2 --}}

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

                    {{-- Résumé groupe --}}
                    <div x-show="isGroup" class="space-y-1.5 pb-3 border-b border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Composition</p>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-if="groupCounts.homme > 0">
                                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-semibold"
                                      x-text="'🧔 ' + groupCounts.homme + ' homme' + (groupCounts.homme > 1 ? 's' : '')"></span>
                            </template>
                            <template x-if="groupCounts.femme > 0">
                                <span class="text-xs px-2 py-0.5 rounded-full bg-pink-50 text-pink-700 font-semibold"
                                      x-text="'👩 ' + groupCounts.femme + ' femme' + (groupCounts.femme > 1 ? 's' : '')"></span>
                            </template>
                            <template x-if="groupCounts.enfant > 0">
                                <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-semibold"
                                      x-text="'🧒 ' + groupCounts.enfant + ' enfant' + (groupCounts.enfant > 1 ? 's' : '')"></span>
                            </template>
                        </div>
                        <p class="text-xs text-gray-400 mt-1"
                           x-text="groupTotalPersons + ' personne(s) — ' + groupTotalGarments + ' vêtement(s)'"></p>
                    </div>

                    {{-- Lignes coûts --}}
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Tissu</span>
                            <span class="font-semibold text-dark" x-text="fmt(fabricCost)"></span>
                        </div>

                        {{-- Main d'œuvre individuelle --}}
                        <div x-show="!isGroup" class="flex justify-between items-center text-sm text-gray-500">
                            <span>Main d'œuvre <span class="text-red-400">*</span></span>
                            <div class="flex items-center gap-1">
                                <input type="number" name="labor_cost" x-model.number="laborCost"
                                       x-on:input="calcTotals"
                                       value="{{ old('labor_cost', 0) }}"
                                       min="0" step="500" placeholder="0"
                                       :required="!isGroup"
                                       class="w-28 px-2 py-1 rounded-lg border border-gray-200 text-xs text-right
                                              focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                                <span class="text-xs text-gray-400" x-text="CURRENCY"></span>
                            </div>
                        </div>

                        {{-- Main d'œuvre groupe (calculée + champ MO globale) --}}
                        <div x-show="isGroup" class="space-y-1.5">
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>MO membres</span>
                                <span class="font-semibold text-dark" x-text="fmt(groupLaborCost)"></span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-500">
                                <span>MO globale (tissu…)</span>
                                <div class="flex items-center gap-1">
                                    <input type="number" name="labor_cost" x-model.number="laborCostExtra"
                                           x-on:input="calcTotals"
                                           min="0" step="500" placeholder="0"
                                           class="w-24 px-2 py-1 rounded-lg border border-gray-200 text-xs text-right
                                                  focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                                    <span class="text-xs text-gray-400" x-text="CURRENCY"></span>
                                </div>
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
        // ── Mode ───────────────────────────────────────────────────
        isGroup:         false,

        // ── Individuel ─────────────────────────────────────────────
        clientId:        '',
        gender:          'femme',
        fabricId:        '',
        fabricMeters:    0,
        laborCost:       0,
        deposit:         0,
        paymentMethod:   'cash',
        measurementId:   '',
        accessories:     [],
        accCounter:      0,

        // ── Groupe ────────────────────────────────────────────────
        groupCounts:     { homme: 0, femme: 0, enfant: 0 },
        groupMembers:    [],
        activeGroupMember: null,
        laborCostExtra:  0, // MO globale (finitions, tissu)

        groupCategories: [
            { key: 'homme', label: '🧔 Adulte homme', sub: 'Mesures homme' },
            { key: 'femme', label: '👩 Adulte femme', sub: 'Mesures femme' },
            { key: 'enfant', label: '🧒 Enfant',       sub: 'Mesures enfant' },
        ],

        // Référentiels mesures
        mesuresHommeHaut: [
            {key:'h_epaule',label:'Épaule'},{key:'h_manche_longue',label:'Manche longue'},
            {key:'h_manche_courte',label:'Manche courte'},{key:'h_tour_poitrine',label:'Tour poitrine'},
            {key:'h_tour_taille_ventre',label:'Tour taille/ventre'},{key:'h_carrure_dos',label:'Carrure dos'},
            {key:'h_longueur_chemise',label:'Long. chemise'},{key:'h_longueur_veste',label:'Long. veste'},
            {key:'h_contour_manche',label:'Contour manche'},{key:'h_tour_col',label:'Tour col'},
            {key:'h_longueur_devant',label:'Long. devant'},
        ],
        mesuresHommeBas: [
            {key:'h_tour_ceinture',label:'Tour ceinture'},{key:'h_tour_bassin',label:'Tour bassin'},
            {key:'h_tour_cuisse',label:'Tour cuisse'},{key:'h_largeur_genoux',label:'Largeur genoux'},
            {key:'h_tour_mollet',label:'Tour mollet'},{key:'h_diametre_bas',label:'Diamètre bas'},
            {key:'h_longueur_pantalon',label:'Long. pantalon'},{key:'h_pisset',label:'Pisset'},
        ],
        mesuresFemmeHaut: [
            {key:'f_longueur_epaule',label:'Long. épaule'},{key:'f_tour_poitrine',label:'Tour poitrine'},
            {key:'f_tour_taille',label:'Tour taille'},{key:'f_petites_hanches',label:'Petites hanches'},
            {key:'f_grandes_hanches',label:'Grandes hanches'},{key:'f_hauteur_saillant',label:'Haut. saillant'},
            {key:'f_ecart_saillants',label:'Écart saillants'},{key:'f_tour_manche',label:'Tour manche'},
            {key:'f_longueur_manche',label:'Long. manche'},{key:'f_carrure_devant',label:'Carrure devant'},
            {key:'f_carrure_dos',label:'Carrure dos'},{key:'f_longueur_veste',label:'Long. veste'},
            {key:'f_tour_encolure',label:'Tour encolure'},{key:'f_hauteur_fessier',label:'Haut. fessier'},
        ],
        mesuresFemmeBas: [
            {key:'f_tour_ceinture',label:'Tour ceinture'},{key:'f_tour_bassin',label:'Tour bassin'},
            {key:'f_hauteur_bassin',label:'Haut. bassin'},{key:'f_entrejambe',label:'Entrejambe'},
            {key:'f_longueur_pantalon',label:'Long. pantalon'},{key:'f_tour_cuisse',label:'Tour cuisse'},
            {key:'f_largeur_bas',label:'Largeur bas'},{key:'f_robe_longue',label:'Robe longue'},
            {key:'f_robe_avant_genoux',label:'Robe av. genoux'},{key:'f_jupe_longue',label:'Jupe longue'},
        ],
        mesuresEnfant: [
            {key:'e_tour_poitrine',label:'Tour poitrine'},{key:'e_tour_taille',label:'Tour taille'},
            {key:'e_tour_hanches',label:'Tour hanches'},{key:'e_epaule',label:'Épaule'},
            {key:'e_longueur_manche',label:'Long. manche'},{key:'e_longueur_veste',label:'Long. veste/robe'},
            {key:'e_tour_ceinture',label:'Tour ceinture'},{key:'e_longueur_pantalon',label:'Long. pantalon'},
            {key:'e_entrejambe',label:'Entrejambe'},{key:'e_tour_cuisse',label:'Tour cuisse'},
        ],

        // ── Computed ───────────────────────────────────────────────
        fabricCost:      0,
        accessoriesCost: 0,
        groupLaborCost:  0,
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
        get activeMember() {
            return this.groupMembers.find(m => m.id === this.activeGroupMember) || null;
        },
        get groupTotalPersons() {
            return this.groupCounts.homme + this.groupCounts.femme + this.groupCounts.enfant;
        },
        get groupTotalGarments() {
            return this.groupMembers.reduce((s, m) => s + m.garments.reduce((gs, g) => gs + (parseInt(g.qty) || 1), 0), 0);
        },
        get isValid() {
            if (this.isGroup) {
                return this.clientId && this.groupTotalPersons > 0 &&
                       document.querySelector('[name=group_name]')?.value;
            }
            return this.clientId && this.gender && document.querySelector('[name=garment_type]')?.value;
        },

        init() {
            this.$watch('fabricId',     () => this.calcTotals());
            this.$watch('fabricMeters', () => this.calcTotals());
            this.$watch('laborCost',    () => this.calcTotals());
            this.$watch('laborCostExtra', () => this.calcTotals());
            this.$watch('isGroup',      () => this.calcTotals());
        },

        // ── Groupe : gestion membres ───────────────────────────────
        changeCount(type, delta) {
            const prev = this.groupCounts[type];
            this.groupCounts[type] = Math.max(0, prev + delta);
            this._rebuildMembers();
            this.$nextTick(() => lucide.createIcons());
        },

        _rebuildMembers() {
            const prefixes = { homme: 'H', femme: 'F', enfant: 'E' };
            const labels   = { homme: 'Homme', femme: 'Femme', enfant: 'Enfant' };
            let newMembers = [];
            ['homme','femme','enfant'].forEach(type => {
                for (let i = 1; i <= this.groupCounts[type]; i++) {
                    const id = prefixes[type] + i;
                    const existing = this.groupMembers.find(m => m.id === id);
                    newMembers.push(existing || {
                        id, type, label: labels[type] + ' ' + i, nom: '',
                        prenom: '', tel: '', pointure: '', age: '', taille_corp: '',
                        notes_speciales: '',
                        measurements: {},
                        garments: [{ garment_type: '', model_name: '', model_description: '', labor_cost: 0, qty: 1, urgence: '' }],
                    });
                }
            });
            this.groupMembers = newMembers;
            if (!this.groupMembers.find(m => m.id === this.activeGroupMember)) {
                this.activeGroupMember = this.groupMembers[0]?.id || null;
            }
            this.calcTotals();
        },

        // ── Individuel ─────────────────────────────────────────────
        onClientChange() {
            this.measurementId = '';
            const c = CLIENTS.find(c => c.id == this.clientId);
            if (c) {
                const def = c.measurements.find(m => m.is_default);
                if (def) this.measurementId = def.id;
            }
        },

        addAccessory() {
            this.accessories.push({ id: ++this.accCounter, name: '', qty: 1, price: 0 });
            this.$nextTick(() => lucide.createIcons());
        },
        removeAccessory(index) {
            this.accessories.splice(index, 1);
            this.calcTotals();
        },

        // ── Calculs ────────────────────────────────────────────────
        calcTotals() {
            // Tissu
            this.fabricCost = (this.fabricId && this.fabricMeters > 0 && this.selectedFabric)
                ? this.selectedFabric.price_per_meter * this.fabricMeters : 0;

            // Accessoires
            this.accessoriesCost = this.accessories.reduce((s, a) => s + ((a.price||0) * (a.qty||1)), 0);

            if (this.isGroup) {
                // MO = somme des vêtements de chaque membre + MO globale
                this.groupLaborCost = this.groupMembers.reduce((s, m) =>
                    s + m.garments.reduce((gs, g) => gs + ((g.labor_cost||0) * (parseInt(g.qty)||1)), 0), 0);
                this.total = this.fabricCost + this.groupLaborCost + (this.laborCostExtra||0) + this.accessoriesCost;
            } else {
                this.total = this.fabricCost + (this.laborCost||0) + this.accessoriesCost;
            }
            this.calcChange();
        },

        calcChange() {
            const dep = this.deposit || 0;
            this.balance = Math.max(0, this.total - dep);
            this.paymentStatus = dep <= 0 ? 'unpaid' : dep >= this.total ? 'paid' : 'partial';
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
