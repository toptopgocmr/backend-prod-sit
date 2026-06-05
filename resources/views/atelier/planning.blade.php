@extends('layouts.app')
@section('title', 'Planning Atelier')

@section('breadcrumb')
    <a href="{{ route('atelier.index') }}" class="hover:text-gray-600 transition-colors">Atelier</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Planning</span>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <i data-lucide="calendar" class="w-5 h-5 text-primary"></i>
            </div>
            <div>
                <h2 class="font-display font-bold text-dark">Planning de livraison</h2>
                <p class="text-xs text-gray-400">{{ $orders->count() }} commande(s) en cours — triées par date</p>
            </div>
        </div>
        <a href="{{ route('atelier.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
            <i data-lucide="arrow-left" style="width:15px;height:15px"></i>
            Atelier
        </a>
    </div>

    @php
        // Grouper par semaine
        $grouped = $orders->groupBy(function($o) {
            if (!$o->delivery_date) return 'Sans date';
            $d = \Carbon\Carbon::parse($o->delivery_date);
            if ($d->isPast() && !$d->isToday()) return 'En retard';
            if ($d->isCurrentWeek()) return 'Cette semaine';
            if ($d->isNextWeek())    return 'Semaine prochaine';
            return $d->format('W') . ' — sem. du ' . $d->startOfWeek()->format('d/m');
        });

        $groupOrder = ['En retard', 'Cette semaine', 'Semaine prochaine'];
        $sorted = collect($groupOrder)
            ->filter(fn($k) => $grouped->has($k))
            ->mapWithKeys(fn($k) => [$k => $grouped[$k]])
            ->merge($grouped->filter(fn($v, $k) => !in_array($k, array_merge($groupOrder, ['Sans date']))));
        if ($grouped->has('Sans date')) $sorted['Sans date'] = $grouped['Sans date'];
    @endphp

    @forelse($sorted as $label => $group)
    @php
        $headerClass = match($label) {
            'En retard'          => 'bg-red-50 border-red-100 text-red-700',
            'Cette semaine'      => 'bg-orange-50 border-orange-100 text-orange-700',
            'Semaine prochaine'  => 'bg-blue-50 border-blue-100 text-blue-700',
            default              => 'bg-gray-50 border-gray-100 text-gray-600',
        };
    @endphp
    <div class="space-y-2">
        <div class="flex items-center gap-2 px-1">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-semibold {{ $headerClass }}">
                @if($label === 'En retard') <i data-lucide="alert-triangle" style="width:12px;height:12px"></i> @endif
                {{ $label }}
            </span>
            <span class="text-xs text-gray-400">{{ $group->count() }} commande(s)</span>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                            <th class="px-5 py-3 text-left font-semibold">Référence</th>
                            <th class="px-5 py-3 text-left font-semibold">Client</th>
                            <th class="px-5 py-3 text-left font-semibold">Couturier</th>
                            <th class="px-5 py-3 text-left font-semibold">Vêtement</th>
                            <th class="px-5 py-3 text-center font-semibold">Statut</th>
                            <th class="px-5 py-3 text-right font-semibold">Date livraison</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($group->sortBy('delivery_date') as $order)
                        @php
                            $isLate  = $order->delivery_date && $order->delivery_date < now()->toDateString();
                            $isToday = $order->delivery_date && $order->delivery_date === now()->toDateString();
                            $statusConfig = match($order->status) {
                                'en_attente' => ['label' => 'En attente', 'class' => 'bg-gray-100 text-gray-600'],
                                'en_cours'   => ['label' => 'En cours',   'class' => 'bg-blue-50 text-blue-700'],
                                'pret'       => ['label' => 'Prête',      'class' => 'bg-green-50 text-green-700'],
                                'essayage'   => ['label' => 'Essayage',   'class' => 'bg-purple-50 text-purple-700'],
                                default      => ['label' => ucfirst($order->status), 'class' => 'bg-gray-50 text-gray-500'],
                            };
                        @endphp
                        <tr class="hover:bg-surface/40 transition-colors {{ $isLate ? 'bg-red-50/20' : '' }}">
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-sm font-bold text-primary">
                                    {{ $order->reference ?? 'CMD-'.$order->id }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-sm font-semibold text-dark">{{ $order->client->full_name ?? '—' }}</p>
                                <p class="text-xs text-gray-400">{{ $order->client->phone ?? '' }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-gray-600">
                                {{ $order->couturier->name ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-sm text-gray-600">
                                {{ $order->garment_type ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="badge-status {{ $statusConfig['class'] }}">{{ $statusConfig['label'] }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if($order->delivery_date)
                                    <p class="text-sm font-semibold {{ $isLate ? 'text-red-600' : ($isToday ? 'text-orange-500' : 'text-dark') }}">
                                        {{ \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') }}
                                    </p>
                                    @if($isLate)
                                        <p class="text-xs text-red-500">{{ \Carbon\Carbon::parse($order->delivery_date)->diffForHumans() }}</p>
                                    @elseif($isToday)
                                        <p class="text-xs text-orange-500 font-semibold">Aujourd'hui !</p>
                                    @else
                                        <p class="text-xs text-gray-400">dans {{ \Carbon\Carbon::parse($order->delivery_date)->diffForHumans(null, true) }}</p>
                                    @endif
                                @else
                                    <span class="text-gray-300 text-sm">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-gray-100 px-5 py-16 text-center text-gray-400">
        <div class="w-16 h-16 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="calendar" class="w-8 h-8 text-gray-200"></i>
        </div>
        <p class="font-medium">Aucune commande en cours</p>
    </div>
    @endforelse

</div>
@endsection
