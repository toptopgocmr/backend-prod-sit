@extends('layouts.app')
@section('title', 'Vente ' . $order->reference)

@section('breadcrumb')
    <a href="{{ route('orders.index') }}" class="hover:text-gray-600 transition-colors">Ventes</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">{{ $order->reference }}</span>
@endsection

@section('content')
@php
$statusColors = [
    'pending'    => 'yellow',
    'confirmed'  => 'blue',
    'processing' => 'indigo',
    'ready'      => 'green',
    'delivered'  => 'emerald',
    'cancelled'  => 'red',
];
$col = $statusColors[$order->status] ?? 'gray';

$payMethod = match($order->payment_method) {
    'cash'         => 'Espèces',
    'mobile_money' => 'Mobile Money',
    'card'         => 'Carte bancaire',
    'credit'       => 'Crédit',
    default        => $order->payment_method ?? '—',
};

$orderTypeLabel = match($order->type) {
    'tissu'         => 'Tissu',
    'pret_a_porter' => 'Prêt-à-porter',
    default         => 'Mixte',
};

$balance = $order->total - $order->amount_paid;
@endphp

<div class="space-y-5">

    {{-- ── Topbar : référence + actions ────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex items-center gap-3 flex-1">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                <i data-lucide="shopping-bag" class="w-5 h-5 text-primary"></i>
            </div>
            <div>
                <h2 class="font-display font-bold text-dark text-base">{{ $order->reference }}</h2>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="badge-status bg-{{ $col }}-50 text-{{ $col }}-700">{{ $order->getStatusLabel() }}</span>
                    <span class="text-xs text-gray-400">{{ $order->created_at->format('d/m/Y à H:i') }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- Changer statut --}}
            @if(!in_array($order->status, ['delivered','cancelled']))
            <form method="POST" action="{{ route('orders.status', $order) }}" class="flex items-center gap-2">
                @csrf @method('PUT')
                <select name="status"
                        class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    @foreach(['pending'=>'En attente','confirmed'=>'Confirmée','processing'=>'En cours','ready'=>'Prête','delivered'=>'Livrée','cancelled'=>'Annulée'] as $val=>$lbl)
                        <option value="{{ $val }}" {{ $order->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="px-3 py-2 rounded-xl bg-dark text-white text-xs font-semibold hover:bg-dark/80 transition-colors">
                    Mettre à jour
                </button>
            </form>
            @endif

            <a href="{{ route('orders.invoice', $order) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                <i data-lucide="file-text" style="width:15px;height:15px"></i>
                Facture
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ══════════════════ COLONNE GAUCHE ══════════════════ --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- ── Articles ─────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="package" class="w-4 h-4 text-primary"></i>
                        Articles
                        <span class="text-xs bg-orange-100 text-primary px-2 py-0.5 rounded-full font-bold">{{ $order->items->count() }}</span>
                    </h3>
                    <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full">{{ $orderTypeLabel }}</span>
                </div>

                <table class="w-full">
                    <thead>
                        <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                            <th class="px-5 py-3 text-left font-semibold">Article</th>
                            <th class="px-5 py-3 text-right font-semibold">Qté</th>
                            <th class="px-5 py-3 text-right font-semibold">P.U.</th>
                            <th class="px-5 py-3 text-right font-semibold">Remise</th>
                            <th class="px-5 py-3 text-right font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($order->items as $item)
                        <tr class="hover:bg-surface/40 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="text-sm font-semibold text-dark">{{ $item->product_name }}</p>
                                @if($item->product)
                                    <p class="text-xs text-gray-400 font-mono">{{ $item->product->reference }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right text-sm font-semibold text-dark">
                                {{ number_format($item->quantity, $item->unit === 'm' ? 2 : 0, ',', ' ') }}
                                <span class="text-xs text-gray-400">{{ $item->unit }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right text-sm text-gray-600">
                                {{ number_format($item->unit_price, 0, ',', ' ') }}
                            </td>
                            <td class="px-5 py-3.5 text-right text-sm text-gray-400">
                                {{ $item->discount > 0 ? '- ' . number_format($item->discount, 0, ',', ' ') : '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right text-sm font-bold text-dark">
                                {{ number_format($item->total, 0, ',', ' ') }}
                                <span class="text-xs font-normal text-gray-400">{{ env('CURRENCY','FCFA') }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-100 bg-gray-50/50">
                            <td colspan="4" class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Sous-total</td>
                            <td class="px-5 py-3 text-right text-sm font-bold text-dark">{{ number_format($order->subtotal, 0, ',', ' ') }} {{ env('CURRENCY','FCFA') }}</td>
                        </tr>
                        @if($order->discount > 0)
                        <tr class="bg-gray-50/50">
                            <td colspan="4" class="px-5 py-2 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Remise</td>
                            <td class="px-5 py-2 text-right text-sm font-semibold text-green-600">- {{ number_format($order->discount, 0, ',', ' ') }} {{ env('CURRENCY','FCFA') }}</td>
                        </tr>
                        @endif
                        <tr class="bg-dark">
                            <td colspan="4" class="px-5 py-3.5 text-right text-sm font-bold text-white uppercase tracking-wider">Total</td>
                            <td class="px-5 py-3.5 text-right text-lg font-black text-primary">{{ number_format($order->total, 0, ',', ' ') }} {{ env('CURRENCY','FCFA') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- ── Historique paiements ─────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="banknote" class="w-4 h-4 text-primary"></i>
                        Paiements
                    </h3>
                    @if($balance > 0)
                        <span class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded-full">
                            Reste : {{ number_format($balance, 0, ',', ' ') }} {{ env('CURRENCY','FCFA') }}
                        </span>
                    @else
                        <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded-full">Soldé</span>
                    @endif
                </div>

                @if($order->payments->count() > 0)
                <table class="w-full">
                    <thead>
                        <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                            <th class="px-5 py-3 text-left font-semibold">Date</th>
                            <th class="px-5 py-3 text-left font-semibold">Mode</th>
                            <th class="px-5 py-3 text-right font-semibold">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($order->payments as $payment)
                        <tr>
                            <td class="px-5 py-3 text-xs text-gray-500">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3 text-sm text-gray-700">
                                {{ match($payment->method) {
                                    'cash'         => 'Espèces',
                                    'mobile_money' => 'Mobile Money',
                                    'card'         => 'Carte',
                                    'credit'       => 'Crédit',
                                    default        => $payment->method,
                                } }}
                            </td>
                            <td class="px-5 py-3 text-right text-sm font-bold text-dark">
                                {{ number_format($payment->amount, 0, ',', ' ') }} {{ env('CURRENCY','FCFA') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="px-5 py-6 text-center text-gray-400 text-sm">
                    <i data-lucide="credit-card" class="w-8 h-8 mx-auto mb-2 text-gray-200"></i>
                    Aucun paiement enregistré
                </div>
                @endif

                {{-- Formulaire nouveau paiement --}}
                @if($balance > 0)
                <div class="px-5 pb-5 pt-3 border-t border-gray-50">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Enregistrer un paiement</p>
                    <form method="POST" action="{{ route('orders.payment', $order) }}" class="flex flex-wrap gap-3">
                        @csrf
                        <input type="number" name="amount" placeholder="Montant" min="1" max="{{ $balance }}"
                               class="flex-1 min-w-32 px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <select name="method" class="px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="cash">Espèces</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="card">Carte</option>
                            <option value="credit">Crédit</option>
                        </select>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-orange-600 active:scale-95 transition-all">
                            Enregistrer
                        </button>
                    </form>
                </div>
                @endif
            </div>

            {{-- ── Livraison ────────────────────────────────── --}}
            @if($order->delivery)
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-primary"></i>
                        Livraison
                    </h3>
                </div>
                <div class="p-5 grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Statut</p>
                        <span class="badge-status bg-blue-50 text-blue-700">{{ ucfirst($order->delivery->status) }}</span>
                    </div>
                    @if($order->delivery->delivery_address)
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Adresse</p>
                        <p class="text-sm font-semibold text-dark">{{ $order->delivery->delivery_address }}</p>
                        @if($order->delivery->delivery_city)
                            <p class="text-xs text-gray-400">{{ $order->delivery->delivery_city }}</p>
                        @endif
                    </div>
                    @endif
                    @if($order->delivery->driver)
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Livreur</p>
                        <div class="flex items-center gap-2">
                            <img src="{{ $order->delivery->driver->avatar_url }}" class="w-6 h-6 rounded-full">
                            <p class="text-sm font-semibold text-dark">{{ $order->delivery->driver->name }}</p>
                        </div>
                    </div>
                    @endif
                    @if($order->delivery->delivery_fee)
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Frais</p>
                        <p class="text-sm font-semibold text-dark">{{ number_format($order->delivery->delivery_fee, 0, ',', ' ') }} {{ env('CURRENCY','FCFA') }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>

        {{-- ══════════════════ COLONNE DROITE ══════════════════ --}}
        <div class="space-y-5">

            {{-- ── Résumé financier ────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-primary"></i>
                        Résumé
                    </h3>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Sous-total</span>
                        <span class="font-semibold text-dark">{{ number_format($order->subtotal, 0, ',', ' ') }}</span>
                    </div>
                    @if($order->discount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Remise</span>
                        <span class="font-semibold text-green-600">- {{ number_format($order->discount, 0, ',', ' ') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm border-t border-gray-100 pt-3">
                        <span class="font-bold text-dark">Total</span>
                        <span class="font-black text-primary text-lg">{{ number_format($order->total, 0, ',', ' ') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Encaissé</span>
                        <span class="font-semibold text-emerald-600">{{ number_format($order->amount_paid, 0, ',', ' ') }}</span>
                    </div>
                    @if($balance > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Reste</span>
                        <span class="font-bold text-orange-600">{{ number_format($balance, 0, ',', ' ') }}</span>
                    </div>
                    @endif

                    <div class="pt-2 border-t border-gray-100">
                        <span class="badge-status w-full justify-center
                            {{ match($order->payment_status) {
                                'paid'    => 'bg-green-50 text-green-700',
                                'partial' => 'bg-yellow-50 text-yellow-700',
                                default   => 'bg-red-50 text-red-700',
                            } }}">
                            {{ match($order->payment_status) {
                                'paid'    => '✓ Payé intégralement',
                                'partial' => '◑ Paiement partiel',
                                default   => '○ Non payé',
                            } }}
                        </span>
                    </div>

                    <div class="text-xs text-gray-400 flex items-center gap-2 pt-1">
                        <i data-lucide="{{ match($order->payment_method ?? '') {
                            'cash'         => 'banknote',
                            'mobile_money' => 'smartphone',
                            'card'         => 'credit-card',
                            default        => 'clock',
                        } }}" style="width:13px;height:13px"></i>
                        {{ $payMethod }}
                    </div>
                </div>
            </div>

            {{-- ── Infos commande ───────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-primary"></i>
                        Informations
                    </h3>
                </div>
                <div class="p-5 space-y-3">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Client</p>
                        @if($order->client)
                        <a href="{{ route('clients.index') }}" class="flex items-center gap-2 group">
                            <div class="w-7 h-7 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                {{ strtoupper(substr($order->client->first_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-dark group-hover:text-primary transition-colors">{{ $order->client->full_name }}</p>
                                <p class="text-xs text-gray-400">{{ $order->client->phone }}</p>
                            </div>
                        </a>
                        @else
                            <p class="text-sm text-gray-400">Client supprimé</p>
                        @endif
                    </div>

                    <div class="border-t border-gray-50 pt-3">
                        <p class="text-xs text-gray-400 mb-1">Caissier</p>
                        <div class="flex items-center gap-2">
                            @if($order->cashier)
                                <img src="{{ $order->cashier->avatar_url }}" class="w-6 h-6 rounded-full">
                                <p class="text-sm font-semibold text-dark">{{ $order->cashier->name }}</p>
                            @else
                                <p class="text-sm text-gray-400">—</p>
                            @endif
                        </div>
                    </div>

                    <div class="border-t border-gray-50 pt-3 grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Créée</p>
                            <p class="text-xs font-semibold text-dark">{{ $order->created_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $order->created_at->format('H:i') }}</p>
                        </div>
                        @if($order->confirmed_at)
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Confirmée</p>
                            <p class="text-xs font-semibold text-dark">{{ $order->confirmed_at->format('d/m/Y') }}</p>
                        </div>
                        @endif
                        @if($order->delivered_at)
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Livrée</p>
                            <p class="text-xs font-semibold text-dark">{{ $order->delivered_at->format('d/m/Y') }}</p>
                        </div>
                        @endif
                    </div>

                    @if($order->notes)
                    <div class="border-t border-gray-50 pt-3">
                        <p class="text-xs text-gray-400 mb-1">Notes</p>
                        <p class="text-sm text-gray-600">{{ $order->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Actions ──────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-2">
                <a href="{{ route('orders.invoice', $order) }}" target="_blank"
                   class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-orange-600 active:scale-95 transition-all">
                    <i data-lucide="file-text" style="width:15px;height:15px"></i>
                    Voir la facture
                </a>
                <a href="{{ route('orders.index') }}"
                   class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                    <i data-lucide="arrow-left" style="width:15px;height:15px"></i>
                    Retour aux ventes
                </a>
                @if(auth()->user()->isAdmin() && !in_array($order->status, ['delivered','cancelled']))
                <form method="POST" action="{{ route('orders.destroy', $order) }}"
                      x-data onsubmit="return confirm('Supprimer cette commande ? Cette action est irréversible.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-red-100 bg-red-50 text-sm font-semibold text-red-600 hover:bg-red-100 transition-colors">
                        <i data-lucide="trash-2" style="width:15px;height:15px"></i>
                        Supprimer
                    </button>
                </form>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
