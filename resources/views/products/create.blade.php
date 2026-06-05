@extends('layouts.app')
@section('title', 'Nouveau produit')

@section('breadcrumb')
    <a href="{{ route('products.index') }}" class="hover:text-gray-600 transition-colors">Produits</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Nouveau produit</span>
@endsection

@section('content')
<div class="max-w-3xl" x-data="{ type: '{{ old('type', 'pret_a_porter') }}', preview: null }">

    {{-- En-tête --}}
    <div class="flex items-center gap-4 mb-5">
        <div class="w-11 h-11 rounded-2xl bg-primary/10 flex items-center justify-center">
            <i data-lucide="package" class="w-5 h-5 text-primary"></i>
        </div>
        <div>
            <h2 class="text-lg font-display font-bold text-dark">Nouveau produit</h2>
            <p class="text-xs text-gray-400">Tissu au mètre, prêt-à-porter ou accessoire</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3.5 rounded-xl text-sm">
        <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 shrink-0 mt-0.5"></i>
        <ul class="space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        {{-- ── 1. Type de produit ────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2 mb-4">
                <i data-lucide="tag" class="w-4 h-4 text-primary"></i>
                Type de produit <span class="text-red-400">*</span>
            </h3>
            <div class="grid grid-cols-3 gap-3">
                @foreach([
                    'pret_a_porter' => ['label' => 'Prêt-à-porter', 'icon' => 'shirt',    'desc' => 'Vêtements, pièces finies'],
                    'tissu'         => ['label' => 'Tissu',          'icon' => 'layers',   'desc' => 'Vendu au mètre'],
                    'accessoire'    => ['label' => 'Accessoire',     'icon' => 'watch',    'desc' => 'Bijoux, sacs, ceintures...'],
                ] as $val => $info)
                <label class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 cursor-pointer transition-all"
                       :class="type === '{{ $val }}' ? 'border-primary bg-orange-50' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="type" value="{{ $val }}" x-model="type" class="sr-only">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all"
                         :class="type === '{{ $val }}' ? 'bg-primary/10' : 'bg-gray-50'">
                        <i data-lucide="{{ $info['icon'] }}" class="w-5 h-5 transition-all"
                           :class="type === '{{ $val }}' ? 'text-primary' : 'text-gray-400'"></i>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-bold transition-all" :class="type === '{{ $val }}' ? 'text-primary' : 'text-dark'">{{ $info['label'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $info['desc'] }}</p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- ── 2. Informations générales ─────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-primary"></i>
                Informations générales
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Nom --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom du produit <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                           placeholder="Ex : Chemise Homme Classique, Pagne Batik Premium..."
                           class="w-full px-3 py-2.5 rounded-xl border @error('name') border-red-300 bg-red-50 @else border-gray-200 @enderror
                                  text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Catégorie --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Catégorie <span class="text-red-400">*</span></label>
                    <select name="category_id" required
                            class="w-full px-3 py-2.5 rounded-xl border @error('category_id') border-red-300 bg-red-50 @else border-gray-200 @enderror
                                   text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        <option value="">— Sélectionner —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Genre --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Genre cible</label>
                    <select name="gender"
                            class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark
                                   focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        <option value="">— Tous —</option>
                        @foreach(['homme'=>'Homme','femme'=>'Femme','enfant_fille'=>'Enfant fille','enfant_garcon'=>'Enfant garçon','mixte'=>'Mixte'] as $v=>$l)
                            <option value="{{ $v }}" {{ old('gender') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Description --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Description</label>
                    <textarea name="description" rows="2" placeholder="Description du produit..."
                              class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark resize-none
                                     focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── 3. Champs selon le type ───────────────────── --}}

        {{-- TISSU --}}
        <div x-show="type === 'tissu'" x-transition class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                <i data-lucide="layers" class="w-4 h-4 text-primary"></i>
                Détails tissu
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prix / mètre ({{ env('CURRENCY','FCFA') }}) <span class="text-red-400">*</span></label>
                    <input type="number" name="price_per_meter" value="{{ old('price_per_meter') }}"
                           min="0" step="50" placeholder="0"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark text-right
                                  focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Stock disponible (m)</label>
                    <input type="number" name="available_meters" value="{{ old('available_meters', 0) }}"
                           min="0" step="0.5" placeholder="0"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark text-right
                                  focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Minimum de coupe (m)</label>
                    <input type="number" name="min_meters" value="{{ old('min_meters', 1) }}"
                           min="0.5" step="0.5" placeholder="1"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark text-right
                                  focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div class="sm:col-span-3 col-span-2"
                     x-data="{
                        colors: {{ json_encode(old('color') ? explode(',', old('color')) : []) }},
                        colorInput: '',
                        presetColors: ['Blanc','Noir','Bleu marine','Rouge','Vert','Beige','Gris','Bordeaux','Kaki','Orange','Pagne wax','Wax imprimé','Bazin','Tissu japonais','Multicolore'],
                        addColor(c) { c=c.trim(); if(c && !this.colors.includes(c)) this.colors.push(c); this.colorInput=''; },
                        removeColor(c) { this.colors=this.colors.filter(x=>x!==c); }
                     }">
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Couleurs / motifs disponibles</label>
                    <div class="flex flex-wrap gap-2 mb-2 min-h-7">
                        <template x-for="c in colors" :key="c">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-orange-50 text-primary text-xs font-bold border border-orange-200">
                                <span x-text="c"></span>
                                <button type="button" x-on:click="removeColor(c)" class="hover:text-red-500 ml-0.5 text-primary/60">×</button>
                            </span>
                        </template>
                        <span x-show="colors.length===0" class="text-xs text-gray-400 italic self-center">Aucune couleur/motif</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5 mb-2">
                        <template x-for="pc in presetColors" :key="pc">
                            <button type="button" x-on:click="addColor(pc)"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold border transition-all"
                                    :class="colors.includes(pc) ? 'border-primary bg-primary/10 text-primary' : 'border-gray-200 text-gray-500 hover:border-primary hover:text-primary'">
                                <span x-text="pc"></span>
                            </button>
                        </template>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" x-model="colorInput" placeholder="Autre couleur/motif..."
                               x-on:keydown.enter.prevent="addColor(colorInput)"
                               class="flex-1 px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        <button type="button" x-on:click="addColor(colorInput)"
                                class="px-3 py-2 rounded-xl bg-primary/10 text-primary text-xs font-bold hover:bg-primary/20 transition-colors">
                            + Ajouter
                        </button>
                    </div>
                    <input type="hidden" name="color" :value="colors.join(',')">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Seuil alerte stock (m) <span class="text-red-400">*</span></label>
                    <input type="number" name="alert_threshold" value="{{ old('alert_threshold', 5) }}"
                           min="0" step="1" placeholder="5" required
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark text-right
                                  focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prix d'achat / m ({{ env('CURRENCY','FCFA') }})</label>
                    <input type="number" name="cost_price" value="{{ old('cost_price') }}"
                           min="0" step="50" placeholder="0"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark text-right
                                  focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
            </div>
        </div>

        {{-- PRÊT-À-PORTER / ACCESSOIRE --}}
        <div x-show="type === 'pret_a_porter' || type === 'accessoire'" x-transition class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                <i data-lucide="shopping-bag" class="w-4 h-4 text-primary"></i>
                Détails article
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prix de vente ({{ env('CURRENCY','FCFA') }}) <span class="text-red-400">*</span></label>
                    <input type="number" name="price" value="{{ old('price') }}"
                           min="0" step="100" placeholder="0"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark text-right
                                  focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Stock initial (pcs)</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 0) }}"
                           min="0" step="1" placeholder="0"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark text-right
                                  focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Seuil alerte stock <span class="text-red-400">*</span></label>
                    <input type="number" name="alert_threshold" value="{{ old('alert_threshold', 3) }}"
                           min="0" step="1" placeholder="3" required
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark text-right
                                  focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                {{-- Tailles multiples --}}
                <div class="sm:col-span-3"
                     x-data="{
                        sizes: {{ json_encode(old('size') ? explode(',', old('size')) : []) }},
                        sizeInput: '',
                        presetSizes: ['XS','S','M','L','XL','XXL','3XL','36','38','40','42','44','46','48','Unique'],
                        addSize(s) { s=s.trim(); if(s && !this.sizes.includes(s)) this.sizes.push(s); this.sizeInput=''; },
                        removeSize(s) { this.sizes=this.sizes.filter(x=>x!==s); }
                     }">
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Tailles disponibles</label>
                    {{-- Tags sélectionnés --}}
                    <div class="flex flex-wrap gap-2 mb-2 min-h-8">
                        <template x-for="s in sizes" :key="s">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold">
                                <span x-text="s"></span>
                                <button type="button" x-on:click="removeSize(s)" class="hover:text-red-500 transition-colors ml-0.5 text-primary/60">×</button>
                            </span>
                        </template>
                        <span x-show="sizes.length===0" class="text-xs text-gray-400 italic self-center">Aucune taille sélectionnée</span>
                    </div>
                    {{-- Tailles prédéfinies --}}
                    <div class="flex flex-wrap gap-1.5 mb-2">
                        <template x-for="ps in presetSizes" :key="ps">
                            <button type="button" x-on:click="addSize(ps)"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold border transition-all"
                                    :class="sizes.includes(ps) ? 'border-primary bg-primary/10 text-primary' : 'border-gray-200 text-gray-500 hover:border-primary hover:text-primary'">
                                <span x-text="ps"></span>
                            </button>
                        </template>
                    </div>
                    {{-- Saisie libre --}}
                    <div class="flex gap-2">
                        <input type="text" x-model="sizeInput" placeholder="Autre taille personnalisée..."
                               x-on:keydown.enter.prevent="addSize(sizeInput)"
                               class="flex-1 px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        <button type="button" x-on:click="addSize(sizeInput)"
                                class="px-3 py-2 rounded-xl bg-primary/10 text-primary text-xs font-bold hover:bg-primary/20 transition-colors">
                            + Ajouter
                        </button>
                    </div>
                    <input type="hidden" name="size" :value="sizes.join(',')">
                </div>

                {{-- Couleurs multiples --}}
                <div class="sm:col-span-3"
                     x-data="{
                        colors: {{ json_encode(old('color') ? explode(',', old('color')) : []) }},
                        colorInput: '',
                        presetColors: [
                            {name:'Noir',hex:'#1a1a1a'},{name:'Blanc',hex:'#f5f5f5'},{name:'Blanc cassé',hex:'#faf8f0'},
                            {name:'Beige',hex:'#d4b896'},{name:'Gris',hex:'#9ca3af'},{name:'Marine',hex:'#1e3a5f'},
                            {name:'Bleu ciel',hex:'#7dd3fc'},{name:'Rouge',hex:'#dc2626'},{name:'Bordeaux',hex:'#9b1c1c'},
                            {name:'Vert',hex:'#16a34a'},{name:'Kaki',hex:'#6b7c40'},{name:'Marron',hex:'#92400e'},
                            {name:'Orange',hex:'#ea580c'},{name:'Jaune',hex:'#ca8a04'},{name:'Rose',hex:'#db2777'},
                            {name:'Violet',hex:'#7c3aed'},{name:'Pagne wax',hex:'#E8820C'},{name:'Multicolore',hex:'linear-gradient'},
                        ],
                        addColor(name) { name=name.trim(); if(name && !this.colors.includes(name)) this.colors.push(name); this.colorInput=''; },
                        removeColor(c) { this.colors=this.colors.filter(x=>x!==c); }
                     }">
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Couleurs disponibles</label>
                    {{-- Tags sélectionnés --}}
                    <div class="flex flex-wrap gap-2 mb-2 min-h-8">
                        <template x-for="c in colors" :key="c">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100 text-dark text-xs font-bold">
                                <span class="w-3 h-3 rounded-full border border-gray-300 shrink-0"
                                      :style="'background:' + (presetColors.find(p=>p.name===c)?.hex || '#888')"></span>
                                <span x-text="c"></span>
                                <button type="button" x-on:click="removeColor(c)" class="hover:text-red-500 transition-colors ml-0.5 text-gray-400">×</button>
                            </span>
                        </template>
                        <span x-show="colors.length===0" class="text-xs text-gray-400 italic self-center">Aucune couleur sélectionnée</span>
                    </div>
                    {{-- Palette prédéfinie --}}
                    <div class="flex flex-wrap gap-2 mb-2">
                        <template x-for="pc in presetColors" :key="pc.name">
                            <button type="button" x-on:click="addColor(pc.name)"
                                    class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-xs font-semibold transition-all"
                                    :class="colors.includes(pc.name) ? 'border-primary bg-primary/5 text-primary' : 'border-gray-200 text-gray-600 hover:border-gray-400'">
                                <span class="w-3 h-3 rounded-full border border-gray-200 shrink-0"
                                      :style="'background:' + (pc.hex.includes('gradient') ? 'linear-gradient(135deg,#f87171,#60a5fa,#4ade80)' : pc.hex)"></span>
                                <span x-text="pc.name"></span>
                            </button>
                        </template>
                    </div>
                    {{-- Saisie libre --}}
                    <div class="flex gap-2">
                        <input type="text" x-model="colorInput" placeholder="Autre couleur personnalisée..."
                               x-on:keydown.enter.prevent="addColor(colorInput)"
                               class="flex-1 px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        <button type="button" x-on:click="addColor(colorInput)"
                                class="px-3 py-2 rounded-xl bg-primary/10 text-primary text-xs font-bold hover:bg-primary/20 transition-colors">
                            + Ajouter
                        </button>
                    </div>
                    <input type="hidden" name="color" :value="colors.join(',')">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prix d'achat ({{ env('CURRENCY','FCFA') }})</label>
                    <input type="number" name="cost_price" value="{{ old('cost_price') }}"
                           min="0" step="100" placeholder="0"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark text-right
                                  focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
            </div>
        </div>

        {{-- ── 4. Photo + Options ─────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                <i data-lucide="image" class="w-4 h-4 text-primary"></i>
                Photo &amp; options
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- Upload photo avec preview --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Photo du produit</label>
                    <div class="flex items-start gap-3">
                        {{-- Preview --}}
                        <div x-show="preview" class="w-24 h-24 rounded-xl border-2 border-primary/20 overflow-hidden shrink-0 relative">
                            <img :src="preview" class="w-full h-full object-cover">
                            <button type="button"
                                    x-on:click="preview=null; $refs.imgInput.value='';"
                                    class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs font-bold hover:bg-red-600">×</button>
                        </div>
                        {{-- Zone upload --}}
                        <label x-show="!preview"
                               class="flex-1 flex flex-col items-center justify-center h-24 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer
                                      hover:border-primary hover:bg-orange-50 transition-all">
                            <i data-lucide="image-plus" class="w-6 h-6 text-gray-300 mb-1"></i>
                            <span class="text-xs text-gray-400 font-medium">Choisir une photo</span>
                            <span class="text-xs text-gray-300">JPG, PNG — max 2 Mo</span>
                            <input type="file" name="image" accept="image/*" class="hidden" x-ref="imgInput"
                                   x-on:change="const f=$event.target.files[0];if(f){const r=new FileReader();r.onload=e=>preview=e.target.result;r.readAsDataURL(f);}">
                        </label>
                        <div x-show="preview" class="flex-1 text-xs text-primary font-semibold flex items-center gap-1 mt-8">
                            <i data-lucide="check-circle" style="width:13px;height:13px"></i>
                            Photo sélectionnée
                        </div>
                    </div>
                </div>

                {{-- Options --}}
                <div class="space-y-3">
                    <label class="block text-xs font-semibold text-gray-600">Options</label>

                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                               class="w-4 h-4 rounded accent-orange-500">
                        <div>
                            <p class="text-sm font-semibold text-dark">Produit actif</p>
                            <p class="text-xs text-gray-400">Visible à la vente et en stock</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1"
                               {{ old('is_featured') == '1' ? 'checked' : '' }}
                               class="w-4 h-4 rounded accent-orange-500">
                        <div>
                            <p class="text-sm font-semibold text-dark">Produit vedette</p>
                            <p class="text-xs text-gray-400">Mis en avant sur le tableau de bord</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- ── Boutons ────────────────────────────────────── --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('products.index') }}"
               class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                Annuler
            </a>
            <button type="submit"
                    class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-bold
                           hover:bg-orange-600 active:scale-95 transition-all shadow-sm shadow-primary/20">
                <i data-lucide="save" style="width:15px;height:15px"></i>
                Créer le produit
            </button>
        </div>

    </form>
</div>
@endsection
