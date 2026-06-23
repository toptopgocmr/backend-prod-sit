@extends('layouts.app')
@section('title', 'Bons de commande')

@section('breadcrumb')
    <a href="{{ route('stock.index') }}" class="hover:text-gray-600 transition-colors">Stock</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Bons de commande</span>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <i data-lucide="shopping-cart" class="w-5 h-5 text-primary"></i>
            </div>
            <div>
                <h2 class="font-display font-bold text-dark">Bons de commande fournisseurs</h2>
                <p class="text-xs text-gray-400">{{ $orders->total() }} bon(s) enregistré(s)</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchase-orders.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-orange-600 transition-colors">
                <i data-lucide="plus" style="width:15px;height:15px"></i>
                Nouveau bon
            </a>
            <a href="{{ route('stock.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                <i data-lucide="arrow-left" style="width:15px;height:15px"></i>
                Stock
            </a>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-100 text-green-700 px-4 py-3 rounded-xl text-sm font-medium">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Tableau --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Référence</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Fournisseur</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Articles</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Montant total</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Statut</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Date</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                    @php
                        $statusConfig = match($order->status) {
                            'ordered'   => ['label' => 'Commandé',  'class' => 'bg-blue-50 text-blue-700'],
                            'received'  => ['label' => 'Reçu',      'class' => 'bg-green-50 text-green-700'],
                            'partial'   => ['label' => 'Partiel',   'class' => 'bg-orange-50 text-orange-700'],
                            'cancelled' => ['label' => 'Annulé',    'class' => 'bg-red-50 text-red-700'],
                            default     => ['label' => ucfirst($order->status), 'class' => 'bg-gray-50 text-gray-600'],
                        };
                    @endphp
                    <tr class="hover:bg-surface/40 transition-colors">
                        <td class="px-5 py-4">
                            <span class="font-mono text-sm font-semibold text-dark">{{ $order->reference }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                    <i data-lucide="building-2" style="width:14px;height:14px" class="text-gray-400"></i>
                                </div>
                                <span class="text-sm font-medium text-dark">{{ $order->supplier_name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-xs font-bold text-gray-600">
                                {{ $order->items->count() }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="text-sm font-bold text-dark">
                                {{ number_format($order->total_amount, 0, ',', ' ') }}
                            </span>
                            <span class="text-xs text-gray-400 font-normal"> FCFA</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="badge-status {{ $statusConfig['class'] }}">
                                {{ $statusConfig['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm text-gray-600">{{ $order->created_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $order->created_at->format('H:i') }}</p>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('purchase-orders.show', $order) }}"
                                   class="p-1.5 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600 transition-colors" title="Voir détail">
                                    <i data-lucide="eye" style="width:15px;height:15px"></i>
                                </a>
                                @if($order->status !== 'received' && $order->status !== 'cancelled')
                                    <a href="{{ route('purchase-orders.edit', $order) }}"
                                       class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark transition-colors" title="Modifier">
                                        <i data-lucide="edit-2" style="width:15px;height:15px"></i>
                                    </a>
                                    {{-- Annuler commande (admin + stock_manager) --}}
                                    <form method="POST" action="{{ route('purchase-orders.cancel', $order) }}"
                                          onsubmit="return confirm('Annuler ce bon de commande après audit / contre-expertise ?')">
                                        @csrf @method('PUT')
                                        <button type="submit"
                                                class="p-1.5 rounded-lg hover:bg-orange-50 text-gray-400 hover:text-orange-600 transition-colors" title="Annuler la commande">
                                            <i data-lucide="x-circle" style="width:15px;height:15px"></i>
                                        </button>
                                    </form>
                                    @if(auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('purchase-orders.destroy', $order) }}"
                                          onsubmit="return confirm('Supprimer définitivement ce bon de commande ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors" title="Supprimer définitivement">
                                            <i data-lucide="trash-2" style="width:15px;height:15px"></i>
                                        </button>
                                    </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </