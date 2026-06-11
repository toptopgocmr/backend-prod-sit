@extends('layouts.app')
@section('title', 'Rapports')

@section('breadcrumb')
    <span class="text-dark font-semibold">Rapports</span>
@endsection


@section('content')
@php
    $periodLabel = $period === 'year' ? "Année $year"
        : ($period === 'week' ? "Semaine en cours"
        : \Carbon\Carbon::create($year, $month)->translatedFormat('F Y'));

    // Couleurs cohérentes
    $palette = ['#C9A84C','#0A0A0A','#34C759','#FF9500','#FF3B30','#007AFF','#5856D6','#AF52DE'];
@endphp

<div class="space-y-5">

{{-- ── Sélecteur période ──────────────────────────────────────── --}}
<form method="GET" action="{{ route('reports.index') }}" class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Période</label>
            <select name="period" id="period-select" onchange="toggleMonthField()"
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                <option value="week"  {{ $period==='week'  ? 'selected':'' }}>Cette semaine</option>
                <option value="month" {{ $period==='month' ? 'selected':'' }}>Mensuel</option>
                <option value="year"  {{ $period==='year'  ? 'selected':'' }}>Annuel</option>
            </select>
        </div>
        <div id="month-field" class="{{ $period==='year' ? 'hidden':'' }}">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Mois</label>
            <select name="month" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $month==$m ? 'selected':'' }}>
                        {{ \Carbon\Carbon::create(null,$m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Année</label>
            <select name="year" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none">
                @foreach(range(date('Y'), date('Y')-3) as $y)
                    <option value="{{ $y }}" {{ $year==$y ? 'selected':'' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold">Générer</button>

        <div class="ml-auto flex items-center gap-2 flex-wrap">
            <span class="text-xs text-gray-400 font-medium">Exporter :</span>
            <a href="{{ route('reports.export', array_merge(request()->query(), ['type'=>'full','format'=>'pdf'])) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-red-200 bg-red-50 text-red-700 text-xs font-semibold hover:bg-red-100 transition-colors">
               <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Rapport complet PDF
            </a>
            <a href="{{ route('reports.export', array_merge(request()->query(), ['type'=>'sales','format'=>'pdf'])) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50 transition-colors">
               <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i> Ventes
            </a>
            <a href="{{ route('reports.export', array_merge(request()->query(), ['type'=>'stock','format'=>'pdf'])) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50 transition-colors">
               <i data-lucide="package" class="w-3.5 h-3.5"></i> Stock
            </a>
            <a href="{{ route('reports.export', array_merge(request()->query(), ['type'=>'expenses','format'=>'pdf'])) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50 transition-colors">
               <i data-lucide="trending-down" class="w-3.5 h-3.5"></i> Dépenses
            </a>
        </div>
    </div>
</form>

<p class="text-xs text-gray-400 font-medium px-1">Période : <span class="text-primary font-bold">{{ $periodLabel }}</span></p>

{{-- ══════════════════════════════════════════════════════
     KPI RÉSUMÉ GLOBAL
════════════════════════════════════════════════════════ --}}
@php
    $profit = $salesData['revenue'] - $expenseData['total'];
    $margin = $salesData['revenue'] > 0 ? round(($profit / $salesData['revenue']) * 100, 1) : 0;
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Chiffre d'affaires</p>
        <p class="text-2xl font-black text-dark">{{ number_format($salesData['revenue'],0,',',' ') }}</p>
        <p class="text-xs text-gray-400 mt-0.5">FCFA</p>
        @if($salesData['evolution'] !== null)
        <span class="inline-flex items-center gap-1 mt-2 text-xs font-semibold px-2 py-0.5 rounded-full {{ $salesData['evolution'] >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">
            {{ $salesData['evolution'] >= 0 ? '▲' : '▼' }} {{ abs($salesData['evolution']) }}%
        </span>
        @endif
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Total dépenses</p>
        <p class="text-2xl font-black text-red-600">{{ number_format($expenseData['total'],0,',',' ') }}</p>
        <p class="text-xs text-gray-400 mt-0.5">FCFA</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Bénéfice net</p>
        <p class="text-2xl font-black {{ $profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
            {{ number_format($profit,0,',',' ') }}
        </p>
        <p class="text-xs text-gray-400 mt-0.5">FCFA</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Marge</p>
        <p class="text-2xl font-black {{ $margin >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $margin }}%</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ $salesData['total_orders'] }} commandes</p>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     VENTES & COMMANDES
════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-display font-bold text-dark flex items-center gap-2 mb-5">
        <i data-lucide="shopping-bag" class="w-5 h-5 text-primary"></i> Ventes & Commandes
    </h2>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-primary/5 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Chiffre d'affaires</p>
            <p class="text-lg font-bold text-dark">{{ number_format($salesData['revenue'],0,',',' ') }}</p>
            <p class="text-xs text-gray-400">FCFA</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Graphique répartition par type --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Répartition par type</p>
            @if(array_sum(array_values($salesData['by_type'])) > 0)
            <div class="flex items-center gap-6">
                <div class="relative" style="width:160px;height:160px;flex-shrink:0">
                    <canvas id="salesTypeChart"></canvas>
                </div>
                <div class="space-y-2 flex-1">
                    @foreach($salesData['by_type'] as $label => $amount)
                    @php $pct = $salesData['revenue'] > 0 ? round(($amount/$salesData['revenue'])*100,1) : 0; @endphp
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full shrink-0" style="background:{{ $palette[$loop->index % count($palette)] }}"></div>
                        <span class="flex-1 text-sm text-gray-700 truncate">{{ $label }}</span>
                        <span class="text-sm font-bold text-dark whitespace-nowrap">{{ number_format($amount,0,',',' ') }} F</span>
                        <span class="text-xs text-gray-400 w-10 text-right">{{ $pct }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
                <p class="text-sm text-gray-400 italic">Aucune vente sur cette période</p>
            @endif
        </div>

        {{-- Tableau top produits --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Top produits vendus</p>
            @if($salesData['top_products']->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-2 text-xs text-gray-400 font-semibold w-8">#</th>
                            <th class="text-left py-2 text-xs text-gray-400 font-semibold">Produit</th>
                            <th class="text-right py-2 text-xs text-gray-400 font-semibold">Qté</th>
                            <th class="text-right py-2 text-xs text-gray-400 font-semibold">CA (FCFA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salesData['top_products'] as $i => $p)
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="py-2.5 text-xs text-gray-400 font-medium">{{ $i+1 }}</td>
                            <td class="py-2.5 text-dark font-medium max-w-[160px] truncate">{{ $p->name }}</td>
                            <td class="py-2.5 text-right text-gray-600">{{ $p->qty }}</td>
                            <td class="py-2.5 text-right font-bold text-dark">{{ number_format($p->total,0,',',' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="pt-3 text-xs text-gray-400 font-semibold">TOTAL</td>
                            <td class="pt-3 text-right font-black text-primary">{{ number_format($salesData['revenue'],0,',',' ') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
                <p class="text-sm text-gray-400 italic">Aucun produit vendu</p>
            @endif
        </div>
    </div>

    {{-- Graphique barres top produits --}}
    @if($salesData['top_products']->count())
    <div class="mt-6">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">CA par produit</p>
        <div style="height:220px">
            <canvas id="topProductsChart"></canvas>
        </div>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════
     STOCK
════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-display font-bold text-dark flex items-center gap-2 mb-5">
        <i data-lucide="package" class="w-5 h-5 text-amber-500"></i> Stock
    </h2>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-amber-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Valeur du stock</p>
            <p class="text-lg font-bold text-dark">{{ number_format($stockData['total_value'],0,',',' ') }}</p>
            <p class="text-xs text-gray-400">FCFA</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Achats période</p>
            <p class="text-lg font-bold text-dark">{{ number_format($stockData['purchases_total'],0,',',' ') }}</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Graphique mouvements --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Mouvements par type</p>
            @if($stockData['movements']->count())
            <div style="height:200px">
                <canvas id="stockMovChart"></canvas>
            </div>
            @else
                <p class="text-sm text-gray-400 italic">Aucun mouvement sur la période</p>
            @endif
        </div>

        {{-- Tableau produits actifs --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Produits les plus actifs</p>
            @if($stockData['top_moving']->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-2 text-xs text-gray-400 font-semibold w-8">#</th>
                            <th class="text-left py-2 text-xs text-gray-400 font-semibold">Produit</th>
                            <th class="text-right py-2 text-xs text-gray-400 font-semibold">Mouvements</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockData['top_moving'] as $i => $p)
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="py-2.5 text-xs text-gray-400 font-medium">{{ $i+1 }}</td>
                            <td class="py-2.5 text-dark font-medium truncate max-w-[180px]">{{ $p->name }}</td>
                            <td class="py-2.5 text-right">
                                <span class="inline-block bg-amber-50 text-amber-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $p->total_mvt }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <p class="text-sm text-gray-400 italic">Aucun mouvement</p>
            @endif
        </div>
    </div>

    {{-- Graphique barres produits actifs --}}
    @if($stockData['top_moving']->count())
    <div class="mt-6">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Activité des produits</p>
        <div style="height:200px">
            <canvas id="topMovingChart"></canvas>
        </div>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════
     DÉPENSES
════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-display font-bold text-dark flex items-center gap-2 mb-5">
        <i data-lucide="trending-down" class="w-5 h-5 text-red-500"></i> Dépenses
    </h2>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-red-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Total dépenses</p>
            <p class="text-lg font-bold text-red-600">{{ number_format($expenseData['total'],0,',',' ') }}</p>
            <p class="text-xs text-gray-400">FCFA</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Opérations</p>
            <p class="text-lg font-bold text-dark">{{ number_format($expenseData['operations'],0,',',' ') }}</p>
            <p class="text-xs text-gray-400">FCFA</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Salaires</p>
            <p class="text-lg font-bold text-dark">{{ number_format($expenseData['salaries'],0,',',' ') }}</p>
            <p class="text-xs text-gray-400">FCFA</p>
        </div>
        <div class="bg-amber-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Achats stock</p>
            <p class="text-lg font-bold text-amber-700">{{ number_format($expenseData['purchases'],0,',',' ') }}</p>
            <p class="text-xs text-gray-400">FCFA</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Camembert catégories --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Répartition par catégorie</p>
            @if($expenseData['by_category']->count())
            <div class="flex items-center gap-6">
                <div class="relative" style="width:160px;height:160px;flex-shrink:0">
                    <canvas id="expenseCatChart"></canvas>
                </div>
                <div class="space-y-2 flex-1">
                    @foreach($expenseData['by_category'] as $cat)
                    @php $pct = $expenseData['total'] > 0 ? round(($cat->total/$expenseData['total'])*100,1) : 0; @endphp
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full shrink-0" style="background:{{ $cat->color ?? $palette[$loop->index % count($palette)] }}"></div>
                        <span class="flex-1 text-sm text-gray-700 truncate">{{ $cat->name }}</span>
                        <span class="text-sm font-bold text-dark whitespace-nowrap">{{ number_format($cat->total,0,',',' ') }} F</span>
                        <span class="text-xs text-gray-400 w-10 text-right">{{ $pct }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
                <p class="text-sm text-gray-400 italic">Aucune dépense</p>
            @endif
        </div>

        {{-- Tableau dépenses récentes --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Dépenses récentes</p>
            @if($expenseData['recent']->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-2 text-xs text-gray-400 font-semibold">Libellé</th>
                            <th class="text-left py-2 text-xs text-gray-400 font-semibold">Catégorie</th>
                            <th class="text-center py-2 text-xs text-gray-400 font-semibold">Date</th>
                            <th class="text-right py-2 text-xs text-gray-400 font-semibold">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenseData['recent'] as $e)
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="py-2.5 text-dark font-medium truncate max-w-[140px]">{{ $e->label }}</td>
                            <td class="py-2.5 text-xs text-gray-500">{{ $e->category->name ?? '—' }}</td>
                            <td class="py-2.5 text-xs text-gray-400 text-center whitespace-nowrap">{{ $e->expense_date->format('d/m/Y') }}</td>
                            <td class="py-2.5 text-right font-bold text-red-600 whitespace-nowrap">{{ number_format($e->amount,0,',',' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="pt-3 text-xs text-gray-400 font-semibold">TOTAL</td>
                            <td class="pt-3 text-right font-black text-red-600">{{ number_format($expenseData['total'],0,',',' ') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
                <p class="text-sm text-gray-400 italic">Aucune dépense</p>
            @endif
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     ANALYSE FINANCIÈRE
════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-display font-bold text-dark flex items-center gap-2 mb-5">
        <i data-lucide="bar-chart-2" class="w-5 h-5 text-blue-500"></i> Analyse financière
    </h2>
    <div style="height:260px">
        <canvas id="financeChart"></canvas>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     MAINTENANCE
════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-display font-bold text-dark flex items-center gap-2 mb-5">
        <i data-lucide="wrench" class="w-5 h-5 text-purple-500"></i> Maintenance
    </h2>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-purple-50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1 font-medium">Coût total</p>
            <p class="text-lg font-bold text-purple-700">{{ number_format($maintenanceData['total_cost'],0,',',' ') }}</p>
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

    @if($maintenanceData['count'] > 0)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Graphique statut --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Statut des interventions</p>
            <div style="height:180px">
                <canvas id="maintenanceStatusChart"></canvas>
            </div>
        </div>

        {{-- Tableau interventions --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Interventions récentes</p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-2 text-xs text-gray-400 font-semibold">Équipement</th>
                            <th class="text-center py-2 text-xs text-gray-400 font-semibold">Date</th>
                            <th class="text-center py-2 text-xs text-gray-400 font-semibold">Statut</th>
                            <th class="text-right py-2 text-xs text-gray-400 font-semibold">Coût</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($maintenanceData['logs'] as $log)
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="py-2.5 text-dark font-medium truncate max-w-[130px]">{{ $log->equipment->name ?? '—' }}</td>
                            <td class="py-2.5 text-xs text-gray-400 text-center whitespace-nowrap">{{ $log->created_at->format('d/m/Y') }}</td>
                            <td class="py-2.5 text-center">
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                                    {{ $log->status === 'resolu' ? 'bg-emerald-50 text-emerald-700'
                                    : ($log->status === 'en_cours' ? 'bg-blue-50 text-blue-700'
                                    : 'bg-orange-50 text-orange-700') }}">
                                    {{ $log->status === 'resolu' ? 'Résolu' : ($log->status === 'en_cours' ? 'En cours' : 'Ouvert') }}
                                </span>
                            </td>
                            <td class="py-2.5 text-right font-bold text-purple-600 whitespace-nowrap">
                                {{ $log->cost > 0 ? number_format($log->cost,0,',',' ').' F' : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="pt-3 text-xs text-gray-400 font-semibold">TOTAL COÛTS</td>
                            <td class="pt-3 text-right font-black text-purple-700">{{ number_format($maintenanceData['total_cost'],0,',',' ') }} F</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

</div>{{-- /space-y-5 --}}

<script>
function toggleMonthField() {
    const period = document.getElementById('period-select').value;
    document.getElementById('month-field').classList.toggle('hidden', period === 'year');
}

// ── Palette ──────────────────────────────────────────
const COLORS = ['#C9A84C','#0A0A0A','#34C759','#FF9500','#FF3B30','#007AFF','#5856D6','#AF52DE'];
const COLORS_ALPHA = COLORS.map(c => c + '22');

const defaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
};

// ── 1. Répartition ventes par type (Doughnut) ────────
@php $byTypeValues = array_values($salesData['by_type']); $byTypeLabels = array_keys($salesData['by_type']); @endphp
@if(array_sum($byTypeValues) > 0)
new Chart(document.getElementById('salesTypeChart'), {
    type: 'doughnut',
    data: {
        labels: @json($byTypeLabels),
        datasets: [{ data: @json($byTypeValues), backgroundColor: COLORS, borderWidth: 2, borderColor: '#fff' }],
    },
    options: { ...defaults, cutout: '68%', plugins: { legend: { display: false }, tooltip: { callbacks: {
        label: (ctx) => ` ${ctx.label}: ${ctx.parsed.toLocaleString('fr-FR')} F`
    }}}}
});
@endif

// ── 2. CA par produit (Bar horizontal) ───────────────
@if($salesData['top_products']->count())
new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
        labels: @json($salesData['top_products']->pluck('name')),
        datasets: [{
            label: 'CA (FCFA)',
            data: @json($salesData['top_products']->pluck('total')),
            backgroundColor: '#C9A84C',
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: { ...defaults, indexAxis: 'y', scales: {
        x: { grid: { color: '#f3f4f6' }, ticks: { callback: v => v.toLocaleString('fr-FR') }},
        y: { grid: { display: false }, ticks: { font: { size: 11 }}}
    }, plugins: { legend: { display: false }, tooltip: { callbacks: {
        label: (ctx) => ` ${ctx.parsed.x.toLocaleString('fr-FR')} FCFA`
    }}}}
});
@endif

// ── 3. Mouvements stock (Bar) ─────────────────────────
@if($stockData['movements']->count())
new Chart(document.getElementById('stockMovChart'), {
    type: 'bar',
    data: {
        labels: @json($stockData['movements']->pluck('type')),
        datasets: [
            { label: 'Opérations', data: @json($stockData['movements']->pluck('count')), backgroundColor: '#007AFF', borderRadius: 6, borderSkipped: false },
            { label: 'Quantités',  data: @json($stockData['movements']->pluck('total_qty')), backgroundColor: '#C9A84C', borderRadius: 6, borderSkipped: false },
        ]
    },
    options: { ...defaults, scales: {
        x: { grid: { display: false }},
        y: { grid: { color: '#f3f4f6' }},
    }, plugins: { legend: { display: true, position: 'top', labels: { font: { size: 11 }, boxWidth: 12 }},
        tooltip: { mode: 'index', intersect: false }
    }}
});
@endif

// ── 4. Produits actifs (Bar) ──────────────────────────
@if($stockData['top_moving']->count())
new Chart(document.getElementById('topMovingChart'), {
    type: 'bar',
    data: {
        labels: @json($stockData['top_moving']->pluck('name')),
        datasets: [{ label: 'Mouvements', data: @json($stockData['top_moving']->pluck('total_mvt')), backgroundColor: '#FF9500', borderRadius: 6, borderSkipped: false }]
    },
    options: { ...defaults, indexAxis: 'y', scales: {
        x: { grid: { color: '#f3f4f6' }},
        y: { grid: { display: false }, ticks: { font: { size: 11 }}}
    }, plugins: { legend: { display: false }}}
});
@endif

// ── 5. Dépenses par catégorie (Doughnut) ──────────────
@if($expenseData['by_category']->count())
@php
    $catColors = $expenseData['by_category']->map(fn($c,$i) => $c->color ?? $palette[$i % count($palette)])->values()->toArray();
@endphp
new Chart(document.getElementById('expenseCatChart'), {
    type: 'doughnut',
    data: {
        labels: @json($expenseData['by_category']->pluck('name')),
        datasets: [{ data: @json($expenseData['by_category']->pluck('total')), backgroundColor: @json($catColors), borderWidth: 2, borderColor: '#fff' }],
    },
    options: { ...defaults, cutout: '68%', plugins: { legend: { display: false }, tooltip: { callbacks: {
        label: (ctx) => ` ${ctx.label}: ${ctx.parsed.toLocaleString('fr-FR')} F`
    }}}}
});
@endif

// ── 6. Analyse financière CA vs Dépenses (Bar groupé) ─
new Chart(document.getElementById('financeChart'), {
    type: 'bar',
    data: {
        labels: ['{{ $periodLabel }}'],
        datasets: [
            { label: 'Chiffre d\'affaires', data: [{{ $salesData['revenue'] }}], backgroundColor: '#C9A84C', borderRadius: 8, borderSkipped: false },
            { label: 'Dépenses',            data: [{{ $expenseData['total'] }}], backgroundColor: '#FF3B30', borderRadius: 8, borderSkipped: false },
            { label: 'Bénéfice',            data: [{{ max(0, $salesData['revenue'] - $expenseData['total']) }}], backgroundColor: '#34C759', borderRadius: 8, borderSkipped: false },
        ]
    },
    options: { ...defaults, scales: {
        x: { grid: { display: false }},
        y: { grid: { color: '#f3f4f6' }, ticks: { callback: v => v.toLocaleString('fr-FR') }},
    }, plugins: { legend: { display: true, position: 'top', labels: { font: { size: 12 }, boxWidth: 14, padding: 16 }},
        tooltip: { callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('fr-FR')} FCFA` }}
    }}
});

// ── 7. Maintenance statut (Doughnut) ─────────────────
@if($maintenanceData['count'] > 0)
new Chart(document.getElementById('maintenanceStatusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Résolues', 'En attente'],
        datasets: [{ data: [{{ $maintenanceData['resolved'] }}, {{ $maintenanceData['pending'] }}], backgroundColor: ['#34C759','#FF9500'], borderWidth: 2, borderColor: '#fff' }],
    },
    options: { ...defaults, cutout: '65%', plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12 }},
        tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.parsed}` }}
    }}
});
@endif
</script>
@endsection
