@extends('layouts.app')
@section('title', 'Mouvements de stock')

@section('breadcrumb')
    <a href="{{ route('stock.index') }}" class="hover:text-gray-600 transition-colors">Stock</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Mouvements</span>
@endsection

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <i data-lucide="activity" class="w-5 h-5 text-primary"></i>
            </div>
            <div>
                <h2 class="font-display font-bold text-dark">Mouvements de stock</h2>
                <p class="text-xs text-gray-400">{{ $movements->total() }} mouvement(s) enregistré(s)</p>
            </div>
        </div>
        <a href="{{ route('stock.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
            <i data-lucide="arrow-left" style="width:15px;height:15px"></i>
            Retour au stock
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Type</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Produit</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Quantité</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Avant → Après</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Raison / Référence</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Utilisateur</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($movements as $mv)
                    @php
                        $isTissu = $mv->product?->type === 'tissu';
                        $unit = $isTissu ? 'm' : 'pcs';
                        $typeConfig = match($mv->type) {
                            'entree','in' => ['label'=>'Entrée',     'color'=>'green',  'icon'=>'arrow-down-circle'],
                            'sortie','out' => ['label'=>'Sortie',    'color'=>'red',    'icon'=>'arrow-up-circle'],
                            'adjustment'  => ['label'=>'Ajustement', 'color'=>'blue',   'icon'=>'sliders'],
                            default        => ['label'=>ucfirst($mv->type), 'color'=>'gray', 'icon'=>'circle'],
                        };
                    @endphp
                    <tr class="hover:bg-surface/40 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center bg-{{ $typeConfig['color'] }}-50">
                                    <i data-lucide="{{ $typeConfig['icon'] }}" style="width:14px;height:14px"
                                       class="text-{{ $typeConfig['color'] }}-600"></i>
                                </div>
                                <span class="text-xs font-semibold text-{{ $typeConfig['color'] }}-700">{{ $typeConfig['label'] }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($mv->product)
                                <a href="{{ route('products.show', $mv->product) }}" class="text-sm font-semibold text-dark hover:text-primary transition-colors">
                                    {{ $mv->product->name }}
                                </a>
                                <p class="text-xs text-gray-400 font-mono">{{ $mv->product->reference }}</p>
                            @else
                                <span class="text-xs text-gray-400 italic">Produit supprimé</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <span class="text-sm font-bold {{ in_array($mv->type, ['entree','in']) ? 'text-green-600' : ($mv->type === 'adjustment' ? 'text-blue-600' : 'text-red-500') }}">
                                {{ in_array($mv->type, ['entree','in']) ? '+' : ($mv->type === 'adjustment' ? '~' : '-') }}{{ number_format($mv->quantity, $isTissu ? 1 : 0, ',', ' ') }} {{ $unit }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right text-xs text-gray-500">
                            @if($mv->quantity_before !== null)
                                {{ number_format($mv->quantity_before, $isTissu ? 1 : 0, ',', ' ') }}
                                <span class="text-gray-300 mx-1">→</span>
                                {{ number_format($mv->quantity_after, $isTissu ? 1 : 0, ',', ' ') }} {{ $unit }}
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-sm text-dark">{{ $mv->reason ?: '—' }}</p>
                            @if($mv->reference)
                                <p class="text-xs text-gray-400 font-mono">{{ $mv->reference }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            @if($mv->user)
                                <div class="flex items-center gap-2">
                                    <img src="{{ $mv->user->avatar_url }}" class="w-6 h-6 rounded-full shrink-0">
                                    <span class="text-xs text-gray-600">{{ $mv->user->name }}</span>
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic">Système</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <p class="text-xs text-gray-600 font-medium">{{ $mv->created_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $mv->created_at->format('H:i') }}</p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center text-gray-400">
                            <i data-lucide="activity" class="w-10 h-10 mx-auto mb-3 text-gray-200"></i>
                            <p>Aucun mouvement de stock enregistré</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">{{ $movements->links() }}</div>
        @endif
    </div>

</div>
@endsection
