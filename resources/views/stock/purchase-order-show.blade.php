@extends('layouts.app')
@section('title', 'Bon de commande — ' . $purchaseOrder->reference)

@section('breadcrumb')
    <a href="{{ route('stock.index') }}" class="hover:text-gray-600 transition-colors">Stock</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <a href="{{ route('purchase-orders.index') }}" class="hover:text-gray-600 transition-colors">Bons de commande</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">{{ $purchaseOrder->reference }}</span>
@endsection

@section('content')
@php
    $statusConfig = match($purchaseOrder->status) {
        'ordered'   => ['label' => 'Commandé',  'class' => 'bg-blue-50 text-blue-700',   'icon' => 'clock'],
        'received'  => ['label' => 'Reçu',      'class' => 'bg-green-50 text-green-700', 'icon' => 'check-circle'],
        'partial'   => ['label' => 'Partiel',   'class' => 'bg-orange-50 text-orange-700','icon' => 'alert-circle'],
        'cancelled' => ['label' => 'Annulé',    'class' => 'bg-red-50 text-red-700',     'icon' => 'x-circle'],
        default     => ['label' => ucfirst($purchaseOrder->status), 'class' => 'bg-gray-50 text-gray-600', 'icon' => 'circle'],
    };
@endphp
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <i data-lucide="file-text" class="w-5 h-5 text-primary"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-display font-bold text-dark">{{ $purchaseOrder->reference }}</h2>
                    <span class="badge-status {{ $statusConfig['class'] }}">
                        {{ $statusConfig['label'] }}
                    </span>
                </div>
                <p class="text-xs text-gray-400">Créé le {{ $purchaseOrder->created_at->format('d/m/Y à H:i') }}</p>
            </div>
        </div>
        <a href="{{ route('purchase-orders.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
            <i data-lucide="arrow-left" style="width:15px;height:15px"></i>
            Retour
        </a>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-100 text-green-700 px-4 py-3 rounded-xl text-sm font-medium">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Infos --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <p class="text-xs text-gray-400 mb-1">Fournisseur</p>
            <div class="flex items-center gap-2">
                <i data-lucide="building-2" class="w-4 h-4 text-gray-300"></i>
                <p class="font-semibold text-dark">{{ $purchaseOrder->supplier_name }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <p class="text-xs text-gray-400 mb-1">Montant total</p>
            <p class="text-xl font-display font-bold text-dark">
                {{ number_format($purchaseOrder->total_amount, 0, ',', ' ') }}
                <span class="text-sm text-gray-400 font-normal">FCFA</span>
            </p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <p class="text-xs text-gray-400 mb-1">Date de réception</p>
            <p class="font-semibold text-dark">
                {{ $purchaseOrder->received_date ? \Carbon\Carbon::parse($purchaseOrder->received_date)->format('d/m/Y') : '—' }}
            </p>
        </div>
    </div>

    {{-- Articles --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-display font-bold text-dark text-sm">Articles commandés</h3>
            <span class="text-xs text-gray-400">{{ $purchaseOrder->items->count() }} article(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Produit</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Qté commandée</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Qté reçue</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Coût unitaire</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Total ligne</th>
                        @if($purchaseOrder->status !== 'received')
                            <th class="px-5 py-3.5 text-center font-semibold">À réceptionner</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($purchaseOrder->items as $item)
                    @php
                        $isTissu    = $item->product?->type === 'tissu';
                        $unit       = $isTissu ? 'm' : 'pcs';
                        $remaining  = $item->quantity_ordered - $item->quantity_received;
                        $isComplete = $remaining <= 0;
                    @endphp
                    <tr class="hover:bg-surface/40 transition-colors">
                        <td class="px-5 py-4">
                            @if($item->product)
                                <a href="{{ route('products.show', $item->product) }}" class="font-semibold text-dark hover:text-primary transition-colors text-sm">
                                    {{ $item->product_name }}
                                </a>
                                <p class="text-xs text-gray-400 font-mono">{{ $item->product->reference }}</p>
                            @else
                                <span class="text-sm font-semibold text-dark">{{ $item->product_name }}</span>
                                <p class="text-xs text-gray-400 italic">Produit supprimé</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-dark">
                            {{ number_format($item->quantity_ordered, $isTissu ? 1 : 0, ',', ' ') }} {{ $unit }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="text-sm font-semibold {{ $isComplete ? 'text-green-600' : 'text-orange-500' }}">
                                {{ number_format($item->quantity_received, $isTissu ? 1 : 0, ',', ' ') }} {{ $unit }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right text-sm text-gray-600">
                            {{ number_format($item->unit_cost, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-dark">
                            {{ number_format($item->total, 0, ',', ' ') }} FCFA
                        </td>
                        @if($purchaseOrder->status !== 'received')
                        <td class="px-5 py-4 text-center">
                            @if($isComplete)
                                <span class="badge-status bg-green-50 text-green-700">Complet</span>
                            @else
                                <input type="number" form="reception-form"
                                       name="items[{{ $item->id }}]"
                                       min="0" max="{{ $remaining }}" step="{{ $isTissu ? '0.1' : '1' }}"
                                       placeholder="{{ number_format($remaining, $isTissu ? 1 : 0, ',', ' ') }} {{ $unit }}"
                                       class="w-28 px-2 py-1.5 border border-gray-200 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-primary/20">
                            @endif
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Formulaire réception --}}
    @if($purchaseOrder->status !== 'received')
    <form id="reception-form" method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}">
        @csrf @method('PUT')
        <div class="flex justify-end">
            <button type="submit"
                    onclick="return confirm('Confirmer la réception ? Le stock sera mis à jour automatiquement.')"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition-colors">
                <i data-lucide="check-circle" style="width:15px;height:15px"></i>
                Confirmer la réception
            </button>
        </div>
    </form>
    @endif

</div>
@endsection
