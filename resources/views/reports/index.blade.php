@extends('layouts.app')
@section('title', 'Rapports')

@section('breadcrumb')
    <span class="text-dark font-semibold">Rapports</span>
@endsection

@section('content')
<div class="space-y-5">

{{-- Sélecteur période --}}
<form method="GET" action="{{ route('reports.index') }}" class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Période</label>
            <select name="period" id="period-select" onchange="toggleMonthField()"
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                <option value="week"  {{ $period === 'week'  ? 'selected' : '' }}>Cette semaine</option>
                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Mensuel</option>
                <option value="year"  {{ $period === 'year'  ? 'selected' : '' }}>Annuel</option>
            </select>
        </div>
        <div id="month-field" class="{{ $period === 'year' ? 'hidden' : '' }}">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Mois</label>
            <select name="month" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Année</label>
            <select name="year" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                @foreach(range(date('Y'), date('Y')-3) as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold">Générer</button>

        {{-- Boutons export --}}
        <div class="ml-auto flex items-center gap-2 flex-wrap">
            <span class="text-xs text-gray-400 font-medium">Exporter :</span>
            <a href="{{ route('reports.export', array_merge(request()->query(), ['type'=>'full','format'=>'pdf'])) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-red-200 bg-red-50 text-red-700 text-xs font-semibold hover:bg-red-100 transition-colors">
               <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Rapport complet PDF
            </a>
            <a href="{{ route('reports.export', array_merge(request()->query(), ['type'=>'sales','format'=>'pdf'])) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50 transition-colors">
               <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i> Ventes
            </a>
            <a href="{{ route('reports.export', array_merge(request()->query(), ['type'=>'stock','format'=>'pdf'])) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50 transition-colors">
               <i data-lucide="package" class="w-3.5 h-3.5"></i> Stock
            </a>
            <a href="{{ route('reports.export', array_merge(request()->query(), ['type'=>'expenses','format'=>'pdf'])) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50 transition-colors">
               <i data-lucide="trending-down" class="w-3.5 h-3.5"></i> Dépenses
            </a>
        </div>
    </div>
</form>

{{-- Période affichée --}}
@php
    $periodLabel = $period === 'year' ? "Année $year"
        : ($period === 'week' ? "Semaine en cours"
        : \Carbon\Carbon::create($year, $month)->translatedFormat('F Y'));
@endphp
<p class="text-xs text-gray-400 font-medium px-1">Période : <span class="text-primary font-bold">{{ $periodLabel }}</span></p>

{{-- ═══════════════════ VENTES ═══════════════════ --}}
<div class="bg-white rounded-2xl border border-gray-100 p-6">
    <div class="flex items-center justify-between mb-5">
        <h2 class="font-display font-bold text-dark flex items-center gap-2">
            <i data-lucide="shopping-bag" class="w-5 h-5 text-primary"></i> Ventes & Commandes
        </h2>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
        <div class="bg-primary/5 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Chiffre d'affaires</p>
            <p class="text-lg font-bold text-dark">{{ number_format($salesData['revenue'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400">FCFA</p>
            @if($salesData['evolution'] !== null)
                <p class="text-xs mt-1 font-semibold {{ $salesData['evolution'] >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                    {{ $salesData['evolution'] >= 0 ? '▲' : '▼' }} {{ abs($salesData['evolution']) }}% vs période préc.
                </p>
            @endif
        </div>
        <div class="bg-gray-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Commandes normales</p>
            <p class="text-lg font-bold text-dark">{{ $salesData['orders_count'] }}</p>
            <p class="text-xs text-gray-400">commandes</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Sur mesure</p>
            <p class="text-lg font-bold text-dark">{{ $salesData['custom_count'] }}</p>
            <p class="text-xs text-gray-400">commandes</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Total commandes</p>
            <p class="text-lg font-bold text-dark">{{ $salesData['total_orders'] }}</p>
            <p class="text-xs text-gray-400">toutes catégories</p>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        {{-- Répartition ventes --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Répartition par type</p>
            <div class="space-y-2">
                @foreach($salesData['by_type'] as $label => $amount)
                    @php $pct = $salesData['revenue'] > 0 ? ($amount / $salesData['revenue']) * 100 : 0; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-dark font-medium">{{ $label }}</span>
                            <span class="font-bold text-dark">{{ number_format($amount, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-primary h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">{{ round($pct, 1) }}%</p>
                    </div>
                @endforeach
            </div>
        </div>
        {{-- Top produits --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Top produits vendus</p>
            @forelse($salesData['top_products'] as $p)
                <div class="flex items-center justify-between py-1.5 border-b border-gray-50 text-sm">
                    <span class="text-dark truncate max-w-[60%]">{{ $p->name }}</span>
                    <div class="text-right">
                        <span class="font-bold text-dark">{{ number_format($p->total, 0, ',', ' ') }}</span>
                        <span class="text-xs text-gray-400 block">{{ $p->qty }} unité(s)</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 italic">Aucune vente sur cette période</p>
            @endforelse
        </div>
    </div>
</div>

{{-- ═══════════════════ STOCK ═══════════════════ --}}
<div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-display font-bold text-dark flex items-center gap-2 mb-5">
        <i data-lucide="package" class="w-5 h-5 text-amber-500"></i> Stock
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
        <div class="bg-amber-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Valeur du stock</p>
            <p class="text-lg font-bold text-dark">{{ number_format($stockData['total_value'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400">FCFA</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Achats période</p>
            <p class="text-lg font-bold text-dark">{{ number_format($stockData['purchases_total'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400">{{ $stockData['purchases']->count() }} bon(s)</p>
        </div>
        <div class="bg-orange-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Stock faible</p>
            <p class="text-lg font-bold text-orange-600">{{ $stockData['low_stock'] }}</p>
            <p class="text-xs text-gray-400">produits</p>
        </div>
        <div class="bg-red-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Ruptures</p>
            <p class="text-lg font-bold text-red-600">{{ $stockData['out_of_stock'] }}</p>
            <p class="text-xs text-gray-400">produits</p>
        </div>
    </div>
    {{-- Mouvements --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Mouvements sur la période</p>
            @forelse($stockData['movements'] as $mvt)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 text-sm">
                    <span class="font-medium text-dark capitalize">{{ $mvt->type }}</span>
                    <span class="text-gray-500">{{ $mvt->count }} opé. — {{ $mvt->total_qty }} unités</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 italic">Aucun mouvement</p>
            @endforelse
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Produits les plus actifs</p>
            @forelse($stockData['top_moving'] as $p)
                <div class="flex items-center justify-between py-1.5 border-b border-gray-50 text-sm">
                    <span class="text-dark truncate max-w-[65%]">{{ $p->name }}</span>
                    <span class="font-bold text-dark">{{ $p->total_mvt }} mvt</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 italic">Aucun mouvement</p>
            @endforelse
        </div>
    </div>
</div>

{{-- ═══════════════════ DÉPENSES ═══════════════════ --}}
<div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-display font-bold text-dark flex items-center gap-2 mb-5">
        <i data-lucide="trending-down" class="w-5 h-5 text-red-500"></i> Dépenses
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
        <div class="bg-red-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Total dépenses</p>
            <p class="text-lg font-bold text-red-600">{{ number_format($expenseData['total'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400">FCFA</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Opérations</p>
            <p class="text-lg font-bold text-dark">{{ number_format($expenseData['operations'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400">FCFA</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Salaires</p>
            <p class="text-lg font-bold text-dark">{{ number_format($expenseData['salaries'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400">FCFA</p>
        </div>
        <div class="bg-amber-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Achats stock</p>
            <p class="text-lg font-bold text-amber-700">{{ number_format($expenseData['purchases'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400">FCFA</p>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Par catégorie</p>
            @forelse($expenseData['by_category'] as $cat)
                <div class="flex items-center gap-3 py-2 border-b border-gray-50">
                    <div class="w-3 h-3 rounded-full shrink-0" style="background: {{ $cat->color }}"></div>
                    <span class="flex-1 text-sm text-dark">{{ $cat->name }}</span>
                    <span class="text-sm font-bold text-dark">{{ number_format($cat->total, 0, ',', ' ') }}</span>
                    <span class="text-xs text-gray-400">({{ $cat->count }})</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 italic">Aucune dépense</p>
            @endforelse
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Dépenses récentes</p>
            @forelse($expenseData['recent'] as $e)
                <div class="flex items-center justify-between py-1.5 border-b border-gray-50 text-sm">
                    <div>
                        <p class="text-dark font-medium truncate max-w-[180px]">{{ $e->label }}</p>
                        <p class="text-xs text-gray-400">{{ $e->expense_date->format('d/m/Y') }} — {{ $e->category->name ?? '—' }}</p>
                    </div>
                    <span class="font-bold text-red-600 whitespace-nowrap">{{ number_format($e->amount, 0, ',', ' ') }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 italic">Aucune dépense</p>
            @endforelse
        </div>
    </div>
</div>

{{-- ═══════════════════ MAINTENANCE ═══════════════════ --}}
<div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-display font-bold text-dark flex items-center gap-2 mb-5">
        <i data-lucide="wrench" class="w-5 h-5 text-purple-500"></i> Maintenance
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
        <div class="bg-purple-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Coût total</p>
            <p class="text-lg font-bold text-purple-700">{{ number_format($maintenanceData['total_cost'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400">FCFA</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Interventions</p>
            <p class="text-lg font-bold text-dark">{{ $maintenanceData['count'] }}</p>
            <p class="text-xs text-gray-400">au total</p>
        </div>
        <div class="bg-emerald-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Résolues</p>
            <p class="text-lg font-bold text-emerald-600">{{ $maintenanceData['resolved'] }}</p>
        </div>
        <div class="bg-orange-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">En attente</p>
            <p class="text-lg font-bold text-orange-600">{{ $maintenanceData['pending'] }}</p>
        </div>
    </div>
    @if($maintenanceData['logs']->count())
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Interventions récentes</p>
    <div class="space-y-1">
        @foreach($maintenanceData['logs'] as $log)
        <div class="flex items-center justify-between py-1.5 border-b border-gray-50 text-sm">
            <div>
                <span class="text-dark font-medium">{{ $log->equipment->name ?? '—' }}</span>
                <span class="text-xs text-gray-400 ml-2">{{ $log->created_at->format('d/m/Y') }}</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs px-2 py-0.5 rounded-full {{ $log->status === 'resolved' ? 'bg-emerald-50 text-emerald-700' : 'bg-orange-50 text-orange-700' }}">
                    {{ $log->status === 'resolved' ? 'Résolu' : 'En cours' }}
                </span>
                @if($log->cost > 0)
                    <span class="font-bold text-purple-600">{{ number_format($log->cost, 0, ',', ' ') }} FCFA</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

</div>

<script>
function toggleMonthField() {
    const period = document.getElementById('period-select').value;
    const mf = document.getElementById('month-field');
    mf.classList.toggle('hidden', period === 'year');
}
</script>
@endsection
