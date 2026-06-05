@extends('layouts.app')
@section('title', 'Tableau de bord')

@section('content')
<div class="space-y-6">

    {{-- ── KPI Cards ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- CA du mois --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-100 card-hover overflow-hidden relative" style="border-left: 4px solid #E8820C;">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-5 h-5 text-primary"></i>
                </div>
                <span class="text-xs font-semibold px-2 py-1 rounded-full
                    {{ $kpis['revenue_growth'] >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                    {{ $kpis['revenue_growth'] >= 0 ? '+' : '' }}{{ $kpis['revenue_growth'] }}%
                </span>
            </div>
            <p class="text-2xl font-display font-bold text-dark">{{ number_format($kpis['revenue_month'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400 mt-1 font-medium">CA du mois (FCFA)</p>
        </div>

        {{-- Bénéfice --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-100 card-hover overflow-hidden relative" style="border-left: 4px solid {{ $kpis['profit_month'] >= 0 ? '#10b981' : '#ef4444' }};">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl {{ $kpis['profit_month'] >= 0 ? 'bg-emerald-50' : 'bg-red-50' }} flex items-center justify-center">
                    <i data-lucide="{{ $kpis['profit_month'] >= 0 ? 'circle-dollar-sign' : 'trending-down' }}"
                       class="w-5 h-5 {{ $kpis['profit_month'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}"></i>
                </div>
                <span class="text-xs text-gray-400">ce mois</span>
            </div>
            <p class="text-2xl font-display font-bold {{ $kpis['profit_month'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                {{ number_format($kpis['profit_month'], 0, ',', ' ') }}
            </p>
            <p class="text-xs text-gray-400 mt-1 font-medium">Bénéfice net (FCFA)</p>
        </div>

        {{-- Ventes today --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-100 card-hover overflow-hidden relative" style="border-left: 4px solid #3b82f6;">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-5 h-5 text-blue-600"></i>
                </div>
                <span class="text-xs text-gray-400">aujourd'hui</span>
            </div>
            <p class="text-2xl font-display font-bold text-dark">{{ $kpis['orders_today'] }}</p>
            <p class="text-xs text-gray-400 mt-1 font-medium">Ventes du jour</p>
        </div>

        {{-- Commandes sur mesure en cours --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-100 card-hover" style="border-left: 4px solid #8b5cf6;">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center">
                    <i data-lucide="scissors" class="w-5 h-5 text-purple-600"></i>
                </div>
                @if($kpis['custom_pending'] > 0)
                    <span class="w-2 h-2 rounded-full bg-orange-400 pulse-dot"></span>
                @endif
            </div>
            <p class="text-2xl font-display font-bold text-dark">{{ $kpis['custom_pending'] }}</p>
            <p class="text-xs text-gray-400 mt-1 font-medium">Sur mesure en cours</p>
        </div>
    </div>

    {{-- ── Graphique + Commandes actives ────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Graphique CA --}}
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-display font-bold text-dark">Évolution du chiffre d'affaires</h3>
                    <p class="text-xs text-gray-400 mt-0.5">12 derniers mois</p>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-500">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-primary inline-block"></span> Revenus</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-gray-200 inline-block"></span> Dépenses</span>
                </div>
            </div>
            <canvas id="revenueChart" height="200"></canvas>
        </div>

        {{-- Alertes stock faible --}}
        <div class="bg-white rounded-2xl p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display font-bold text-dark">Stock faible</h3>
                <a href="{{ route('stock.low') }}" class="text-xs text-primary font-semibold hover:underline">Voir tout</a>
            </div>
            @if($lowStockProducts->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <i data-lucide="check-circle" class="w-8 h-8 mx-auto mb-2 text-green-400"></i>
                    <p class="text-sm">Tous les stocks sont OK</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($lowStockProducts->take(6) as $product)
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full {{ $product->getCurrentStock() == 0 ? 'bg-red-500' : 'bg-orange-400' }} shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-dark truncate">{{ $product->name }}</p>
                                <p class="text-xs text-gray-400">{{ $product->getTypeLabel() }}</p>
                            </div>
                            <span class="text-sm font-bold {{ $product->getCurrentStock() == 0 ? 'text-red-600' : 'text-orange-500' }}">
                                {{ $product->getCurrentStock() }} {{ $product->getStockUnit() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ── Commandes sur mesure + Maintenance ───────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Commandes sur mesure en cours --}}
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50">
                <h3 class="font-display font-bold text-dark">Atelier – En production</h3>
                <a href="{{ route('custom-orders.index') }}" class="text-xs text-primary font-semibold hover:underline">Voir tout</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($activeCustomOrders as $order)
                    <a href="{{ route('custom-orders.show', $order) }}" class="flex items-center gap-4 px-6 py-3.5 hover:bg-surface transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                            <i data-lucide="scissors" class="w-4 h-4 text-purple-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-dark">{{ $order->client->full_name }}</p>
                            <p class="text-xs text-gray-400">{{ $order->reference }} · {{ ucfirst($order->garment_type) }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="badge-status bg-{{ $order->getStatusColor() }}-50 text-{{ $order->getStatusColor() }}-700">
                                {{ $order->getStatusLabel() }}
                            </span>
                            @if($order->delivery_date)
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $order->delivery_date->diffForHumans() }}
                                </p>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-10 text-center text-gray-400 text-sm">
                        Aucune commande en production
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Maintenance + Alertes --}}
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50">
                <h3 class="font-display font-bold text-dark">Maintenance & Équipements</h3>
                <a href="{{ route('maintenance.index') }}" class="text-xs text-primary font-semibold hover:underline">Voir tout</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($equipmentAlerts as $eq)
                    <div class="flex items-center gap-4 px-6 py-3.5">
                        <div class="w-9 h-9 rounded-xl {{ $eq->status === 'en_panne' ? 'bg-red-50' : 'bg-yellow-50' }} flex items-center justify-center shrink-0">
                            <i data-lucide="tool" class="w-4 h-4 {{ $eq->status === 'en_panne' ? 'text-red-600' : 'text-yellow-600' }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-dark">{{ $eq->name }}</p>
                            <p class="text-xs text-gray-400">{{ $eq->getTypeLabel() }} · {{ $eq->location }}</p>
                        </div>
                        <span class="badge-status {{ $eq->status === 'en_panne' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700' }}">
                            {{ match($eq->status) {
                                'en_panne'       => 'En panne',
                                'en_maintenance' => 'En maint.',
                                default          => 'Préventif',
                            } }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-gray-400 text-sm">
                        <i data-lucide="check-circle" class="w-6 h-6 mx-auto mb-2 text-green-400"></i>
                        Tous les équipements sont opérationnels
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Dernières ventes ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50">
            <h3 class="font-display font-bold text-dark">Dernières ventes</h3>
            <a href="{{ route('orders.index') }}" class="text-xs text-primary font-semibold hover:underline">Voir tout</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50">
                        <th class="px-6 py-3 text-left font-semibold">Référence</th>
                        <th class="px-6 py-3 text-left font-semibold">Client</th>
                        <th class="px-6 py-3 text-left font-semibold">Type</th>
                        <th class="px-6 py-3 text-right font-semibold">Total</th>
                        <th class="px-6 py-3 text-center font-semibold">Paiement</th>
                        <th class="px-6 py-3 text-center font-semibold">Statut</th>
                        <th class="px-6 py-3 text-right font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recentOrders as $order)
                        <tr class="hover:bg-surface/50 transition-colors">
                            <td class="px-6 py-3.5">
                                <a href="{{ route('orders.show', $order) }}" class="text-sm font-mono font-semibold text-primary hover:underline">
                                    {{ $order->reference }}
                                </a>
                            </td>
                            <td class="px-6 py-3.5 text-sm text-dark font-medium">{{ $order->client->full_name }}</td>
                            <td class="px-6 py-3.5 text-xs text-gray-500">
                                {{ match($order->type) { 'tissu'=>'Tissu','pret_a_porter'=>'Prêt-à-porter',default=>'Mixte' } }}
                            </td>
                            <td class="px-6 py-3.5 text-sm font-bold text-dark text-right">
                                {{ number_format($order->total, 0, ',', ' ') }} <span class="text-xs text-gray-400 font-normal">FCFA</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="badge-status {{ match($order->payment_status) {
                                    'paid'    => 'bg-green-50 text-green-700',
                                    'partial' => 'bg-yellow-50 text-yellow-700',
                                    default   => 'bg-red-50 text-red-700',
                                } }}">
                                    {{ match($order->payment_status) { 'paid'=>'Payé','partial'=>'Partiel',default=>'Impayé' } }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="badge-status bg-{{ $order->getStatusColor() }}-50 text-{{ $order->getStatusColor() }}-700">
                                    {{ $order->getStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-xs text-gray-400 text-right">{{ $order->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Footer ── --}}
    <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-400">
        <div class="flex items-center gap-2">
            
            <span>© {{ date('Y') }} <strong class="text-gray-500">GSIT</strong> — Plateforme de gestion interne</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span>Développé avec</span>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="#e8820c" stroke="none"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            <span>par <strong class="text-gray-600">Basile Marius Ngassaki Zoni</strong></span>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const data = @json($revenueChart);
    const ctx  = document.getElementById('revenueChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.label),
            datasets: [
                {
                    label: 'Revenus',
                    data: data.map(d => d.revenue),
                    backgroundColor: 'rgba(232,130,12,0.8)',
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Dépenses',
                    data: data.map(d => d.expenses),
                    backgroundColor: 'rgba(229,231,235,0.8)',
                    borderRadius: 6,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => `${ctx.dataset.label}: ${ctx.raw.toLocaleString('fr-FR')} FCFA`
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, border: { display: false } },
                y: {
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    border: { display: false },
                    ticks: { callback: v => (v/1000).toFixed(0) + 'k' }
                }
            }
        }
    });
});
</script>
@endpush
