@extends('layouts.app')
@section('title', 'Stock faible')

@section('breadcrumb')
    <a href="{{ route('stock.index') }}" class="hover:text-gray-600 transition-colors">Stock</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Stock faible</span>
@endsection

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500"></i>
            </div>
            <div>
                <h2 class="font-display font-bold text-dark">Alertes stock faible</h2>
                <p class="text-xs text-gray-400">{{ $products->count() }} produit(s) en dessous du seuil d'alerte</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('stock.movements') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                <i data-lucide="activity" style="width:15px;height:15px"></i>
                Mouvements
            </a>
            <a href="{{ route('stock.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                <i data-lucide="arrow-left" style="width:15px;height:15px"></i>
                Retour
            </a>
        </div>
    </div>

    @if($products->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 px-5 py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-green-50 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="check-circle" class="w-8 h-8 text-green-500"></i>
            </div>
            <h3 class="font-display font-bold text-dark mb-1">Tous les stocks sont suffisants</h3>
            <p class="text-sm text-gray-400">Aucun produit n'est en dessous de son seuil d'alerte.</p>
        </div>
    @else

        {{-- Résumé par type --}}
        @php
            $tissus = $products->where('type', 'tissu');
            $pap    = $products->where('type', '!=', 'tissu');
        @endphp
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-orange-50 border border-orange-100 rounded-2xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
                    <i data-lucide="layers" class="w-5 h-5 text-orange-600"></i>
                </div>
                <div>
                    <p class="text-xl font-display font-bold text-orange-700">{{ $tissus->count() }}</p>
                    <p class="text-xs text-orange-600 font-medium">Tissu(s) en alerte</p>
                </div>
            </div>
            <div class="bg-red-50 border border-red-100 rounded-2xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                    <i data-lucide="package" class="w-5 h-5 text-red-600"></i>
                </div>
                <div>
                    <p class="text-xl font-display font-bold text-red-700">{{ $pap->count() }}</p>
                    <p class="text-xs text-red-600 font-medium">Article(s) en alerte</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                            <th class="px-5 py-3.5 text-left font-semibold">Produit</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Type</th>
                            <th class="px-5 py-3.5 text-right font-semibold">Stock actuel</th>
                            <th class="px-5 py-3.5 text-right font-semibold">Seuil alerte</th>
                            <th class="px-5 py-3.5 text-center font-semibold">Niveau</th>
                            <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($products as $product)
                        @php
                            $isTissu = $product->type === 'tissu';
                            $stock   = $isTissu ? $product->available_meters : $product->stock_quantity;
                            $unit    = $isTissu ? 'm' : 'pcs';
                            $pct     = $product->alert_threshold > 0
                                ? min(100, round(($stock / $product->alert_threshold) * 100))
                                : 0;
                            $isZero  = $stock <= 0;
                        @endphp
                        <tr class="hover:bg-surface/40 transition-colors">
                            <td class="px-5 py-4">
                                <a href="{{ route('products.show', $product) }}" class="flex items-center gap-3 group">
                                    <div class="w-9 h-9 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center shrink-0">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover rounded-xl">
                                        @else
                                            <i data-lucide="{{ $isTissu ? 'layers' : 'package' }}" style="width:16px;height:16px" class="text-gray-300"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-dark group-hover:text-primary transition-colors">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-400 font-mono">{{ $product->reference }}</p>
                                    </div>
                                </a>
                            </td>
                            <td class="px-5 py-4">
                                <span class="badge-status {{ $isTissu ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                    {{ $product->getTypeLabel() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <span class="text-sm font-bold {{ $isZero ? 'text-red-600' : 'text-orange-600' }}">
                                    {{ number_format($stock, $isTissu ? 1 : 0, ',', ' ') }} {{ $unit }}
                                </span>
                                @if($isZero)
                                    <p class="text-xs text-red-500 font-semibold">RUPTURE</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right text-sm text-gray-500">
                                {{ $product->alert_threshold }} {{ $unit }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-100 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all {{ $isZero ? 'bg-red-500' : 'bg-orange-400' }}"
                                             style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold {{ $isZero ? 'text-red-500' : 'text-orange-500' }} shrink-0">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('products.show', $product) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary text-white text-xs font-semibold hover:bg-orange-600 transition-colors">
                                    <i data-lucide="plus-circle" style="width:13px;height:13px"></i>
                                    Réapprovisionner
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
