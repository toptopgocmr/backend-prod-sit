@extends('layouts.app')
@section('title', 'Ventes')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex-1"><p class="text-sm text-gray-500">{{ $orders->total() }} vente(s)</p></div>
        <div class="flex items-center gap-2">
            {{-- Export Excel --}}
            <a href="{{ route('orders.export', array_merge(request()->query(), ['format'=>'excel'])) }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition-colors">
                <i data-lucide="table-2" class="w-4 h-4"></i>
                Excel
            </a>
            {{-- Export PDF --}}
            <a href="{{ route('orders.export', array_merge(request()->query(), ['format'=>'pdf'])) }}"
               target="_blank"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-red-50 hover:border-red-300 hover:text-red-700 transition-colors">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                PDF
            </a>
            <a href="{{ route('quotes.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition-colors">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                Devis
            </a>
            <a href="{{ route('orders.create') }}"
               class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary-600 transition-colors shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i> Nouvelle vente
            </a>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4">
        <div class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Référence, client..."
                   class="flex-1 min-w-48 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                <option value="">Tous statuts</option>
                @foreach(['pending'=>'En attente','confirmed'=>'Confirmée','processing'=>'En cours','ready'=>'Prête','delivered'=>'Livrée','cancelled'=>'Annulée'] as $val=>$label)
                    <option value="{{ $val }}" {{ request('status')==$val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="payment_status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                <option value="">Tous paiements</option>
                <option value="not_paid" {{ request('payment_status')=='not_paid' ? 'selected' : '' }}>Impayé (tous)</option>
                <option value="unpaid"   {{ request('payment_status')=='unpaid'   ? 'selected' : '' }}>Impayé</option>
                <option value="partial"  {{ request('payment_status')=='partial'  ? 'selected' : '' }}>Partiel</option>
                <option value="paid"     {{ request('payment_status')=='paid'     ? 'selected' : '' }}>Payé</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-dark text-white rounded-lg text-sm font-semibold">Filtrer</button>
        </div>
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Référence</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Client</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Type</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Total</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Reste</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Paiement</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Statut</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Date</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                        <tr class="hover:bg-surface/40 transition-colors">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('orders.show', $order) }}" class="font-mono text-sm font-bold text-primary hover:underline">
                                    {{ $order->reference }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-sm font-semibold text-dark">{{ $order->client?->full_name ?? "Client supprimé" }}</p>
                                <p class="text-xs text-gray-400">{{ $order->client?->phone ?? "-" }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-500">
                                {{ match($order->type) { 'tissu'=>'Tissu','pret_a_porter'=>'Prêt-à-porter',default=>'Mixte' } }}
                            </td>
                            <td class="px-5 py-3.5 text-right text-sm font-bold text-dark">{{ number_format($order->total, 0, ',', ' ') }}</td>
                            <td class="px-5 py-3.5 text-right text-sm {{ $order->balance > 0 ? 'text-orange-600 font-bold' : 'text-gray-300' }}">
                                {{ $order->balance > 0 ? number_format($order->balance, 0, ',', ' ') : '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="badge-status {{ match($order->payment_status) { 'paid'=>'bg-green-50 text-green-700','partial'=>'bg-yellow-50 text-yellow-700',default=>'bg-red-50 text-red-700' } }}">
                                    {{ match($order->payment_status) { 'paid'=>'Payé','partial'=>'Partiel',default=>'Impayé' } }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="badge-status bg-{{ $order->getStatusColor() }}-50 text-{{ $order->getStatusColor() }}-700">
                                    {{ $order->getStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right text-xs text-gray-400">{{ $order->created_at->format('d/m/Y') }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('orders.show', $order) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark">
                                        <i data-lucide="eye" style="width:15px;height:15px"></i>
                                    </a>
                                    <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark">
                                        <i data-lucide="file-text" style="width:15px;height:15px"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-16 text-center text-gray-400">
                                <i data-lucide="shopping-bag" class="w-10 h-10 mx-auto mb-3 text-gray-200"></i>
                                <p>Aucune vente trouvée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">{{ $orders->links() }}</div>
        @endif
    </div>

</div>
@endsection
