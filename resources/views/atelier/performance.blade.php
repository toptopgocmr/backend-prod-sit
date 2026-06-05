@extends('layouts.app')
@section('title', 'Performance Atelier')

@section('breadcrumb')
    <a href="{{ route('atelier.index') }}" class="hover:text-gray-600 transition-colors">Atelier</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Performance</span>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <i data-lucide="bar-chart-2" class="w-5 h-5 text-primary"></i>
            </div>
            <div>
                <h2 class="font-display font-bold text-dark">Performance des couturiers</h2>
                <p class="text-xs text-gray-400">Mois de {{ now()->translatedFormat('F Y') }}</p>
            </div>
        </div>
        <a href="{{ route('atelier.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
            <i data-lucide="arrow-left" style="width:15px;height:15px"></i>
            Atelier
        </a>
    </div>

    @php
        $totalCompleted = $perf->sum('completed');
        $totalRevenue   = $perf->sum('revenue');
        $totalOrders    = $perf->sum('total');
    @endphp

    {{-- KPIs globaux --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-xs text-gray-400 mb-2 font-medium">✂️ Couturiers actifs</p>
            <p class="text-2xl font-display font-bold text-dark">{{ $perf->count() }}</p>
            <p class="text-xs text-gray-400 mt-0.5">ce mois</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-xs text-gray-400 mb-2 font-medium">✅ Commandes livrées</p>
            <p class="text-2xl font-display font-bold text-green-600">{{ $totalCompleted }}</p>
            <p class="text-xs text-gray-400 mt-0.5">sur {{ $totalOrders }} total</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-xs text-gray-400 mb-2 font-medium">💰 Revenus main d'œuvre</p>
            <p class="text-2xl font-display font-bold text-dark">{{ number_format($totalRevenue, 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400 mt-0.5">FCFA</p>
        </div>
    </div>

    {{-- Tableau performance --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-display font-bold text-dark text-sm">Détail par couturier</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Couturier</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Commandes</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Livrées</th>
                        <th class="px-5 py-3.5 text-right font-semibold">En cours</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Taux complétion</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Revenus MO</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($perf as $row)
                    @php
                        $rate    = $row->total > 0 ? round(($row->completed / $row->total) * 100) : 0;
                        $pending = $row->total - $row->completed;
                        $barColor = $rate >= 80 ? 'bg-green-500' : ($rate >= 50 ? 'bg-orange-400' : 'bg-red-400');
                    @endphp
                    <tr class="hover:bg-surface/40 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-sm font-bold text-primary shrink-0">
                                    {{ strtoupper(substr($row->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-semibold text-dark">{{ $row->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-dark">{{ $row->total }}</td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-green-600">{{ $row->completed }}</td>
                        <td class="px-5 py-4 text-right text-sm {{ $pending > 0 ? 'text-orange-500 font-semibold' : 'text-gray-300' }}">
                            {{ $pending > 0 ? $pending : '—' }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-2">
                                    <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width: {{ $rate }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-dark w-9 text-right">{{ $rate }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-dark">
                            {{ number_format($row->revenue ?? 0, 0, ',', ' ') }}
                            <span class="text-xs text-gray-400 font-normal">FCFA</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center text-gray-400">
                            <div class="w-16 h-16 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="bar-chart-2" class="w-8 h-8 text-gray-200"></i>
                            </div>
                            <p class="font-medium">Aucune donnée ce mois</p>
                            <p class="text-xs mt-1">Les performances s'affichent dès qu'une commande est assignée.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($perf->count() > 0)
                <tfoot>
                    <tr class="bg-gray-50/50 border-t border-gray-100">
                        <td class="px-5 py-3.5 text-sm font-semibold text-dark">Total</td>
                        <td class="px-5 py-3.5 text-right text-sm font-bold text-dark">{{ $totalOrders }}</td>
                        <td class="px-5 py-3.5 text-right text-sm font-bold text-green-600">{{ $totalCompleted }}</td>
                        <td class="px-5 py-3.5 text-right text-sm font-bold text-orange-500">{{ $totalOrders - $totalCompleted }}</td>
                        <td class="px-5 py-3.5 text-center text-sm font-bold text-dark">
                            {{ $totalOrders > 0 ? round(($totalCompleted / $totalOrders) * 100) : 0 }}%
                        </td>
                        <td class="px-5 py-3.5 text-right text-sm font-bold text-dark">
                            {{ number_format($totalRevenue, 0, ',', ' ') }}
                            <span class="text-xs text-gray-400 font-normal">FCFA</span>
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection
