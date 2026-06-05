@extends('layouts.app')
@section('title', 'Atelier')

@section('breadcrumb')
    <span class="text-gray-600 font-medium">Atelier</span>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <i data-lucide="scissors" class="w-5 h-5 text-primary"></i>
            </div>
            <div>
                <h2 class="font-display font-bold text-dark">Atelier</h2>
                <p class="text-xs text-gray-400">{{ $orders->count() }} commande(s) en cours</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('atelier.planning') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                <i data-lucide="calendar" style="width:15px;height:15px"></i>
                Planning
            </a>
            <a href="{{ route('atelier.performance') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                <i data-lucide="bar-chart-2" style="width:15px;height:15px"></i>
                Performance
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-100 text-green-700 px-4 py-3 rounded-xl text-sm font-medium">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- KPIs --}}
    @php
        $urgent   = $orders->filter(fn($o) => $o->delivery_date && \Carbon\Carbon::parse($o->delivery_date)->diffInDays(now(), false) >= 0 && $o->delivery_date <= now()->toDateString());
        $thisWeek = $orders->filter(fn($o) => $o->delivery_date && \Carbon\Carbon::parse($o->delivery_date)->isCurrentWeek());
        $byStatus = $orders->groupBy('status');
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-xs text-gray-400 mb-2 font-medium">📋 En attente</p>
            <p class="text-2xl font-display font-bold text-dark">{{ $byStatus->get('en_attente', collect())->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-xs text-gray-400 mb-2 font-medium">✂️ En cours</p>
            <p class="text-2xl font-display font-bold text-blue-600">{{ $byStatus->get('en_cours', collect())->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-xs text-gray-400 mb-2 font-medium">✅ Prêtes</p>
            <p class="text-2xl font-display font-bold text-green-600">{{ $byStatus->get('pret', collect())->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 {{ $urgent->count() > 0 ? 'border-red-200 bg-red-50/30' : '' }}">
            <p class="text-xs text-gray-400 mb-2 font-medium">🚨 Urgentes / en retard</p>
            <p class="text-2xl font-display font-bold {{ $urgent->count() > 0 ? 'text-red-600' : 'text-gray-300' }}">{{ $urgent->count() }}</p>
        </div>
    </div>

    {{-- Tableau des commandes --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-display font-bold text-dark text-sm">Commandes sur mesure en cours</h3>
            <span class="text-xs text-gray-400">Triées par date de livraison</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Référence</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Client</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Couturier</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Vêtement</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Statut</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Livraison prévue</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                    @php
                        $isLate   = $order->delivery_date && $order->delivery_date < now()->toDateString();
                        $isToday  = $order->delivery_date && $order->delivery_date === now()->toDateString();

                        $statusConfig = match($order->status) {
                            'en_attente' => ['label' => 'En attente', 'class' => 'bg-gray-100 text-gray-600'],
                            'en_cours'   => ['label' => 'En cours',   'class' => 'bg-blue-50 text-blue-700'],
                            'pret'       => ['label' => 'Prête',      'class' => 'bg-green-50 text-green-700'],
                            'essayage'   => ['label' => 'Essayage',   'class' => 'bg-purple-50 text-purple-700'],
                            default      => ['label' => ucfirst($order->status), 'class' => 'bg-gray-50 text-gray-500'],
                        };
                    @endphp
                    <tr class="hover:bg-surface/40 transition-colors {{ $isLate ? 'bg-red-50/20' : '' }}">
                        <td class="px-5 py-4">
                            @if(Route::has('custom-orders.show'))
                                <a href="{{ route('custom-orders.show', $order) }}" class="font-mono text-sm font-bold text-primary hover:underline">
                                    {{ $order->reference ?? 'CMD-'.$order->id }}
                                </a>
                            @else
                                <span class="font-mono text-sm font-bold text-dark">{{ $order->reference ?? 'CMD-'.$order->id }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm font-semibold text-dark">{{ $order->client->full_name ?? '—' }}</p>
                            <p class="text-xs text-gray-400">{{ $order->client->phone ?? '' }}</p>
                        </td>
                        <td class="px-5 py-4">
                            @if($order->couturier)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-primary/10 flex items-center justify-center text-xs font-bold text-primary">
                                        {{ strtoupper(substr($order->couturier->name, 0, 1)) }}
                                    </div>
                                    <span class="text-sm text-dark">{{ $order->couturier->name }}</span>
                                </div>
                            @else
                                <span class="text-xs text-gray-300 italic">Non assigné</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-600">
                            {{ $order->garment_type ?? '—' }}
                            @if($order->fabric_name ?? false)
                                <p class="text-xs text-gray-400">{{ $order->fabric_name }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="badge-status {{ $statusConfig['class'] }}">{{ $statusConfig['label'] }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($order->delivery_date)
                                <p class="text-sm font-semibold {{ $isLate ? 'text-red-600' : ($isToday ? 'text-orange-500' : 'text-dark') }}">
                                    {{ \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') }}
                                </p>
                                @if($isLate)
                                    <p class="text-xs text-red-500">
                                        {{ \Carbon\Carbon::parse($order->delivery_date)->diffForHumans() }}
                                    </p>
                                @elseif($isToday)
                                    <p class="text-xs text-orange-500">Aujourd'hui</p>
                                @else
                                    <p class="text-xs text-gray-400">
                                        dans {{ \Carbon\Carbon::parse($order->delivery_date)->diffForHumans(null, true) }}
                                    </p>
                                @endif
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @if(Route::has('custom-orders.show'))
                                    <a href="{{ route('custom-orders.show', $order) }}"
                                       class="p-1.5 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600 transition-colors" title="Voir">
                                        <i data-lucide="eye" style="width:15px;height:15px"></i>
                                    </a>
                                @endif
                                @if(Route::has('custom-orders.edit'))
                                    <a href="{{ route('custom-orders.edit', $order) }}"
                                       class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark transition-colors" title="Modifier">
                                        <i data-lucide="edit-2" style="width:15px;height:15px"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center text-gray-400">
                            <div class="w-16 h-16 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="scissors" class="w-8 h-8 text-gray-200"></i>
                            </div>
                            <p class="font-medium">Aucune commande en cours</p>
                            <p class="text-xs mt-1">Toutes les commandes sont livrées ou annulées.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Charge par couturier --}}
    @if($couturiers->count() > 0)
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h3 class="font-display font-bold text-dark text-sm mb-4 flex items-center gap-2">
            <i data-lucide="users" class="w-4 h-4 text-gray-400"></i>
            Charge de travail par couturier
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($couturiers as $couturier)
            @php
                $assigned = $orders->where('assigned_to', $couturier->id)->count();
            @endphp
            <div class="flex items-center gap-3 p-4 rounded-xl border border-gray-100 bg-gray-50/40">
                <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-sm font-bold text-primary shrink-0">
                    {{ strtoupper(substr($couturier->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-dark truncate">{{ $couturier->name }}</p>
                    <p class="text-xs text-gray-400">{{ $assigned }} commande(s) assignée(s)</p>
                </div>
                <span class="text-lg font-display font-bold {{ $assigned > 3 ? 'text-orange-500' : 'text-dark' }}">
                    {{ $assigned }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
