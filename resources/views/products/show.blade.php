@extends('layouts.app')
@section('title', $product->name)

@section('breadcrumb')
    <a href="{{ route('products.index') }}" class="hover:text-gray-600 transition-colors">Produits</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">{{ $product->name }}</span>
@endsection

@section('content')
@php
$currency = env('CURRENCY', 'FCFA');
$isTissu  = $product->type === 'tissu';
$stock    = $isTissu ? $product->available_meters : $product->stock_quantity;
$unit     = $isTissu ? 'm' : 'pcs';
$isLow    = $product->isLowStock();
$colors   = $product->color ? array_map('trim', explode(',', $product->color)) : [];
$sizes    = $product->size  ? array_map('trim', explode(',', $product->size))  : [];
@endphp

<div class="space-y-5">

    {{-- ── Topbar ─────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex items-center gap-4 flex-1">
            <div class="w-14 h-14 rounded-2xl overflow-hidden shrink-0 border border-gray-100 bg-gray-50 flex items-center justify-center">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <i data-lucide="{{ $isTissu ? 'layers' : 'package' }}" class="w-6 h-6 text-gray-300"></i>
                @endif
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl font-display font-bold text-dark">{{ $product->name }}</h2>
                    @if($product->is_featured)
                        <span class="text-xs bg-yellow-50 text-yellow-700 border border-yellow-200 px-2 py-0.5 rounded-full font-semibold">⭐ Vedette</span>
                    @endif
                    <span class="badge-status {{ $product->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $product->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
                <div class="flex items-center gap-3 mt-0.5 flex-wrap text-xs text-gray-400">
                    <span class="font-mono">{{ $product->reference }}</span>
                    <span>{{ $product->getTypeLabel() }}</span>
                    @if($product->category) <span>{{ $product->category->name }}</span> @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('products.toggle', $product) }}">
                @csrf @method('PUT')
                <button type="submit" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold
                       {{ $product->is_active ? 'text-gray-600 hover:bg-gray-50' : 'text-green-600 hover:bg-green-50' }} transition-colors">
                    {{ $product->is_active ? 'Désactiver' : 'Activer' }}
                </button>
            </form>
            <a href="{{ route('products.edit', $product) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-orange-600 active:scale-95 transition-all shadow-sm">
                <i data-lucide="edit-2" style="width:15px;height:15px"></i>
                Modifier
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ══════════ COLONNE GAUCHE ══════════ --}}
        <div class="space-y-5">

            {{-- ── Stock ───────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4 text-primary"></i>
                    Stock
                </h3>
                <div class="text-center py-2">
                    <p class="text-4xl font-display font-black {{ $isLow ? 'text-red-500' : 'text-dark' }}">
                        {{ number_format($stock, $isTissu ? 2 : 0, ',', ' ') }}
                    </p>
                    <p class="text-sm text-gray-400 mt-1">{{ $unit }} disponible{{ $stock > 1 ? 's' : '' }}</p>
                    @if($isLow)
                        <span class="mt-2 inline-flex items-center gap-1.5 text-xs bg-red-50 text-red-600 border border-red-100 px-3 py-1 rounded-full font-semibold">
                            <i data-lucide="alert-triangle" style="width:12px;height:12px"></i>
                            Stock bas — seuil : {{ $product->alert_threshold }} {{ $unit }}
                        </span>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-3">
                    @if($isTissu)
                        <div class="bg-surface rounded-xl p-3 border border-gray-100">
                            <p class="text-xs text-gray-400 mb-1">Prix / m</p>
                            <p class="text-sm font-bold text-dark">{{ number_format($product->price_per_meter, 0, ',', ' ') }} {{ $currency }}</p>
                        </div>
                        <div class="bg-surface rounded-xl p-3 border border-gray-100">
                            <p class="text-xs text-gray-400 mb-1">Coupe min.</p>
                            <p class="text-sm font-bold text-dark">{{ $product->min_meters }} m</p>
                        </div>
                    @else
                        <div class="bg-surface rounded-xl p-3 border border-gray-100">
                            <p class="text-xs text-gray-400 mb-1">Prix de vente</p>
                            <p class="text-sm font-bold text-dark">{{ number_format($product->price, 0, ',', ' ') }} {{ $currency }}</p>
                        </div>
                        <div class="bg-surface rounded-xl p-3 border border-gray-100">
                            <p class="text-xs text-gray-400 mb-1">Seuil alerte</p>
                            <p class="text-sm font-bold text-dark">{{ $product->alert_threshold }} {{ $unit }}</p>
                        </div>
                    @endif
                    @if($product->cost_price)
                        <div class="bg-surface rounded-xl p-3 border border-gray-100">
                            <p class="text-xs text-gray-400 mb-1">Prix d'achat</p>
                            <p class="text-sm font-bold text-dark">{{ number_format($product->cost_price, 0, ',', ' ') }} {{ $currency }}</p>
                        </div>
                        <div class="bg-surface rounded-xl p-3 border border-gray-100">
                            <p class="text-xs text-gray-400 mb-1">Marge</p>
                            @php
                                $unitPrice = $isTissu ? $product->price_per_meter : $product->price;
                                $margin = $unitPrice && $product->cost_price ? round((($unitPrice - $product->cost_price) / $unitPrice) * 100) : null;
                            @endphp
                            <p class="text-sm font-bold {{ $margin >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $margin !== null ? $margin . '%' : '—' }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ── Caractéristiques ─────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-3">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="tag" class="w-4 h-4 text-primary"></i>
                    Caractéristiques
                </h3>

                @if($product->gender)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Genre</span>
                    <span class="font-semibold text-dark">
                        {{ match($product->gender) { 'homme'=>'Homme','femme'=>'Femme','enfant_fille'=>'Enfant fille','enfant_garcon'=>'Enfant garçon','mixte'=>'Mixte', default=>$product->gender } }}
                    </span>
                </div>
                @endif

                @if(!empty($sizes))
                <div>
                    <p class="text-xs text-gray-400 mb-1.5">Tailles</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($sizes as $s)
                            <span class="px-2.5 py-1 rounded-lg bg-primary/10 text-primary text-xs font-bold">{{ $s }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!empty($colors))
                <div>
                    <p class="text-xs text-gray-400 mb-1.5">Couleurs / Motifs</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($colors as $c)
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-dark text-xs font-semibold">{{ $c }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($product->description)
                <div class="pt-2 border-t border-gray-50">
                    <p class="text-xs text-gray-400 mb-1">Description</p>
                    <p class="text-sm text-gray-600">{{ $product->description }}</p>
                </div>
                @endif
            </div>

            {{-- ── Actions ─────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-4 space-y-2">
                <a href="{{ route('products.edit', $product) }}"
                   class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200
                          text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                    <i data-lucide="edit-2" style="width:15px;height:15px"></i> Modifier
                </a>
                @if(auth()->user()->isAdmin())
                <form method="POST" action="{{ route('products.destroy', $product) }}"
                      onsubmit="return confirm('Supprimer ce produit ?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-red-100 bg-red-50
                                   text-sm font-semibold text-red-600 hover:bg-red-100 transition-colors">
                        <i data-lucide="trash-2" style="width:15px;height:15px"></i> Supprimer
                    </button>
                </form>
                @endif
            </div>

        </div>

        {{-- ══════════ COLONNE DROITE ══════════ --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- ── Historique mouvements de stock ──── --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="activity" class="w-4 h-4 text-primary"></i>
                        Mouvements de stock
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-bold">{{ $product->stockMovements->count() }}</span>
                    </h3>
                </div>

                @forelse($product->stockMovements as $mv)
                <div class="px-5 py-3 border-b border-gray-50 last:border-0 flex items-center gap-4">
                    {{-- Icône type --}}
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0
                                {{ $mv->type === 'in' ? 'bg-green-50' : ($mv->type === 'adjustment' ? 'bg-blue-50' : 'bg-red-50') }}">
                        <i data-lucide="{{ $mv->type === 'in' ? 'arrow-down-circle' : ($mv->type === 'adjustment' ? 'sliders' : 'arrow-up-circle') }}"
                           class="w-4 h-4 {{ $mv->type === 'in' ? 'text-green-600' : ($mv->type === 'adjustment' ? 'text-blue-600' : 'text-red-500') }}"
                           style="width:16px;height:16px"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-dark">
                            {{ $mv->reason ?: match($mv->type) { 'in'=>'Entrée stock','out'=>'Sortie stock','adjustment'=>'Ajustement', default=>$mv->type } }}
                        </p>
                        <div class="flex items-center gap-2 text-xs text-gray-400 mt-0.5">
                            <span>{{ $mv->created_at->format('d/m/Y H:i') }}</span>
                            @if($mv->reference) <span class="font-mono">{{ $mv->reference }}</span> @endif
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-bold {{ $mv->type === 'in' ? 'text-green-600' : ($mv->type === 'adjustment' ? 'text-blue-600' : 'text-red-500') }}">
                            {{ $mv->type === 'in' ? '+' : ($mv->type === 'adjustment' ? '~' : '-') }}{{ number_format($mv->quantity, $isTissu ? 1 : 0, ',', ' ') }} {{ $unit }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ number_format($mv->quantity_before ?? 0, $isTissu ? 1 : 0, ',', ' ') }}
                            → {{ number_format($mv->quantity_after ?? 0, $isTissu ? 1 : 0, ',', ' ') }} {{ $unit }}
                        </p>
                    </div>
                </div>
                @empty
                <div class="px-5 py-10 text-center text-gray-400 text-sm">
                    <i data-lucide="activity" class="w-8 h-8 mx-auto mb-2 text-gray-200"></i>
                    Aucun mouvement de stock enregistré
                </div>
                @endforelse
            </div>

        </div>
    </div>
</div>
@endsection
