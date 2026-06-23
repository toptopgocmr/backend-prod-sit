@extends('layouts.app')
@section('title', 'Catégories de produits')

@section('breadcrumb')
    <a href="{{ route('products.index') }}" class="hover:text-gray-600 transition-colors">Produits</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Catégories</span>
@endsection

@section('content')
<div class="space-y-5" x-data="{ editId: null, editName: '', editType: '', editIcon: '', editDesc: '', editPrice: 0 }">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-display font-bold text-dark">Catégories de produits</h2>
            <p class="text-xs text-gray-400">{{ $categories->count() }} catégorie(s) enregistrée(s)</p>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
        <i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
        <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Formulaire Nouvelle catégorie ── --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4 text-primary"></i>
                Nouvelle catégorie
            </h3>
            <form method="POST" action="{{ route('categories.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="Ex : Bazin, Soie, Dentelle..."
                           class="w-full px-3 py-2.5 rounded-xl border @error('name') border-red-300 @else border-gray-200 @enderror text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Type <span class="text-red-400">*</span></label>
                    <select name="type" required
                            class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        <option value="">— Choisir —</option>
                        <option value="tissu" {{ old('type')=='tissu' ? 'selected' : '' }}>Tissu</option>
                        <option value="pret_a_porter" {{ old('type')=='pret_a_porter' ? 'selected' : '' }}>Prêt-à-porter</option>
                        <option value="accessoire" {{ old('type')=='accessoire' ? 'selected' : '' }}>Accessoire</option>
                        <option value="autre" {{ old('type')=='autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Icône (emoji)</label>
                    <input type="text" name="icon" value="{{ old('icon') }}"
                           placeholder="🧵 🪡 👗 👔"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Description</label>
                    <input type="text" name="description" value="{{ old('description') }}"
                           placeholder="Description courte..."
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Prix par défaut
                        <span class="font-normal text-gray-400">(FCFA / unité ou mètre)</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="price" value="{{ old('price', 0) }}" min="0" step="100"
                               placeholder="0"
                               class="w-full px-3 py-2.5 pr-16 rounded-xl border border-gray-200 text-sm text-dark text-right focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">FCFA</span>
                    </div>
                </div>
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-bold hover:bg-orange-600 transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Ajouter la catégorie
                </button>
            </form>
        </div>

        {{-- ── Liste des catégories ── --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h3 class="text-sm font-display font-semibold text-dark">Toutes les catégories</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($categories as $cat)
                <div class="px-5 py-4" x-show="editId !== {{ $cat->id }}">
                    <div class="flex items-center gap-3">
                        <span class="text-xl w-8 text-center">{{ $cat->icon ?: '📦' }}</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-dark">{{ $cat->name }}</span>
                                @if(!$cat->is_active)
                                <span class="text-xs bg-gray-100 text-gray-400 px-2 py-0.5 rounded-full">Inactif</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 mt-0.5">
                                <span class="text-xs text-gray-400">
                                    @php $typeLabels = ['tissu'=>'Tissu','pret_a_porter'=>'Prêt-à-porter','accessoire'=>'Accessoire','autre'=>'Autre']; @endphp
                                    {{ $typeLabels[$cat->type] ?? $cat->type }}
                                </span>
                                <span class="text-xs text-gray-300">•</span>
                                <span class="text-xs text-gray-400">{{ $cat->products_count }} produit(s)</span>
                                @if($cat->price > 0)
                                <span class="text-xs text-gray-300">•</span>
                                <span class="text-xs font-semibold text-primary">
                                    {{ number_format($cat->price, 0, ',', ' ') }} FCFA
                                </span>
                                @endif
                                @if($cat->description)
                                <span class="text-xs text-gray-300">•</span>
                                <span class="text-xs text-gray-400 truncate">{{ $cat->description }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            {{-- Toggle actif/inactif --}}
                            <form method="POST" action="{{ route('categories.toggle', $cat) }}">
                                @csrf @method('PUT')
                                <button type="submit" title="{{ $cat->is_active ? 'Désactiver' : 'Activer' }}"
                                        class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                                    <i data-lucide="{{ $cat->is_active ? 'eye' : 'eye-off' }}" class="w-4 h-4 {{ $cat->is_active ? 'text-green-500' : 'text-gray-300' }}"></i>
                                </button>
                            </form>
                            {{-- Modifier --}}
                            <button type="button" title="Modifier"
                                    @click="editId={{ $cat->id }}; editName='{{ addslashes($cat->name) }}'; editType='{{ $cat->type }}'; editIcon='{{ $cat->icon }}'; editDesc='{{ addslashes($cat->description ?? '') }}'; editPrice={{ $cat->price ?? 0 }}"
                                    class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                                <i data-lucide="pencil" class="w-4 h-4 text-gray-400"></i>
                            </button>
                            {{-- Supprimer --}}
                            @if(auth()->user()->isAdmin() && $cat->products_count === 0)
                            <form method="POST" action="{{ route('categories.destroy', $cat) }}"
                                  onsubmit="return confirm('Supprimer {{ addslashes($cat->name) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Supprimer"
                                        class="p-1.5 rounded-lg hover:bg-red-50 transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4 text-red-400"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Formulaire d'édition inline --}}
                <div class="px-5 py-4 bg-orange-50/50 border-l-4 border-primary" x-show="editId === {{ $cat->id }}" x-cloak>
                    <form method="POST" action="{{ route('categories.update', $cat) }}" class="space-y-3">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Nom</label>
                                <input type="text" name="name" x-model="editName" required
                                       class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Type</label>
                                <select name="type" x-model="editType"
                                        class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                    <option value="tissu">Tissu</option>
                                    <option value="pret_a_porter">Prêt-à-porter</option>
                                    <option value="accessoire">Accessoire</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Icône</label>
                                <input type="text" name="icon" x-model="editIcon" placeholder="🧵"
                                       class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Description</label>
                                <input type="text" name="description" x-model="editDesc"
                                       class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">
                                    Prix défaut <span class="font-normal text-gray-400">(FCFA)</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="price" x-model.number="editPrice" min="0" step="100"
                                           class="w-full px-3 py-2 pr-14 rounded-xl border border-gray-200 text-sm text-dark text-right focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">FCFA</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary text-white text-sm font-bold hover:bg-orange-600 transition-all">
                                <i data-lucide="save" class="w-3.5 h-3.5"></i> Enregistrer
                            </button>
                            <button type="button" @click="editId=null"
                                    class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition-all">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">
                    Aucune catégorie. Créez-en une à gauche.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
