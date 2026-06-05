@extends('layouts.app')
@section('title', $client->full_name)

@section('breadcrumb')
    <a href="{{ route('clients.index') }}" class="hover:text-gray-600 transition-colors">Clients</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">{{ $client->full_name }}</span>
@endsection

@section('content')
@php
$genderIcon = match($client->gender) { 'homme' => '👨', 'femme' => '👩', default => '👤' };
$genderLabel = match($client->gender) { 'homme' => 'Homme', 'femme' => 'Femme', default => 'Non précisé' };
@endphp

<div class="space-y-5">

    {{-- ── Topbar ────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex items-center gap-4 flex-1">
            {{-- Avatar --}}
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl shrink-0
                        {{ $client->gender === 'femme' ? 'bg-pink-50' : ($client->gender === 'homme' ? 'bg-blue-50' : 'bg-gray-50') }}">
                {{ $genderIcon }}
            </div>
            <div>
                <h2 class="text-xl font-display font-bold text-dark">{{ $client->full_name }}</h2>
                <div class="flex items-center gap-3 mt-0.5 flex-wrap">
                    <span class="text-xs text-gray-400">{{ $genderLabel }}</span>
                    @if($client->city)
                        <span class="text-xs text-gray-400 flex items-center gap-1">
                            <i data-lucide="map-pin" style="width:11px;height:11px"></i>
                            {{ $client->city }}
                        </span>
                    @endif
                    @if($client->birth_date)
                        <span class="text-xs text-gray-400 flex items-center gap-1">
                            <i data-lucide="cake" style="width:11px;height:11px"></i>
                            {{ $client->birth_date->format('d/m/Y') }}
                            ({{ $client->birth_date->age }} ans)
                        </span>
                    @endif
                    <span class="text-xs text-gray-400">Client depuis {{ $client->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('clients.edit', $client) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                <i data-lucide="edit-2" style="width:15px;height:15px"></i>
                Modifier
            </a>
            <a href="{{ route('orders.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-orange-600 active:scale-95 transition-all shadow-sm">
                <i data-lucide="plus" style="width:15px;height:15px"></i>
                Nouvelle vente
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ══════════ COLONNE GAUCHE ══════════ --}}
        <div class="space-y-5">

            {{-- ── Infos contact ───────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-3">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="contact" class="w-4 h-4 text-primary"></i>
                    Contact
                </h3>

                <a href="tel:{{ $client->phone }}"
                   class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                        <i data-lucide="phone" class="w-4 h-4 text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Téléphone</p>
                        <p class="text-sm font-semibold text-dark group-hover:text-primary transition-colors">{{ $client->phone }}</p>
                    </div>
                </a>

                @if($client->email)
                <a href="mailto:{{ $client->email }}"
                   class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                        <i data-lucide="mail" class="w-4 h-4 text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">E-mail</p>
                        <p class="text-sm font-semibold text-dark group-hover:text-primary transition-colors">{{ $client->email }}</p>
                    </div>
                </a>
                @endif

                @if($client->address || $client->city)
                <div class="flex items-center gap-3 p-2">
                    <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center shrink-0">
                        <i data-lucide="map-pin" class="w-4 h-4 text-primary"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Adresse</p>
                        <p class="text-sm font-semibold text-dark">
                            {{ implode(', ', array_filter([$client->address, $client->city])) }}
                        </p>
                    </div>
                </div>
                @endif

                @if($client->notes)
                <div class="mt-2 px-3 py-2.5 bg-amber-50 border border-amber-100 rounded-xl">
                    <p class="text-xs font-semibold text-amber-700 mb-1 flex items-center gap-1">
                        <i data-lucide="sticky-note" style="width:12px;height:12px"></i>
                        Notes
                    </p>
                    <p class="text-xs text-amber-800">{{ $client->notes }}</p>
                </div>
                @endif
            </div>

            {{-- ── Stats ───────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-3">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="bar-chart-2" class="w-4 h-4 text-primary"></i>
                    Statistiques
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-surface rounded-xl p-3 border border-gray-100 text-center">
                        <p class="text-2xl font-display font-black text-dark">{{ $client->orders->count() }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Ventes</p>
                    </div>
                    <div class="bg-surface rounded-xl p-3 border border-gray-100 text-center">
                        <p class="text-2xl font-display font-black text-dark">{{ $client->customOrders->count() }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Sur mesure</p>
                    </div>
                    <div class="col-span-2 bg-primary/5 rounded-xl p-3 border border-primary/10 text-center">
                        <p class="text-xl font-display font-black text-primary">
                            {{ number_format($client->total_spent, 0, ',', ' ') }} {{ env('CURRENCY','FCFA') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">Total dépensé</p>
                    </div>
                </div>
            </div>

            {{-- ── Mesures ─────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="ruler" class="w-4 h-4 text-purple-600"></i>
                        Mesures
                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-bold">{{ $client->measurements->count() }}</span>
                    </h3>
                    <a href="{{ route('custom-orders.create') }}"
                       class="text-xs text-purple-600 font-semibold hover:underline">+ Nouvelle commande</a>
                </div>

                @forelse($client->measurements as $m)
                <div class="px-5 py-3 border-b border-gray-50 last:border-0">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-semibold text-dark flex items-center gap-2">
                            {{ $m->label ?: 'Mesures #' . $m->id }}
                            @if($m->is_default)
                                <span class="text-xs bg-purple-50 text-purple-600 px-1.5 py-0.5 rounded-full font-semibold">Défaut</span>
                            @endif
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                        @foreach($m->toFormattedArray() as $label => $val)
                            @if($val)
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-400">{{ $label }}</span>
                                <span class="font-semibold text-dark">{{ $val }}</span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @empty
                <div class="px-5 py-6 text-center text-gray-400 text-xs">
                    <i data-lucide="ruler" class="w-7 h-7 mx-auto mb-2 text-gray-200"></i>
                    Aucune fiche de mesures
                </div>
                @endforelse
            </div>

            {{-- ── Actions ─────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-4 space-y-2">
                <a href="{{ route('clients.edit', $client) }}"
                   class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200
                          text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                    <i data-lucide="edit-2" style="width:15px;height:15px"></i>
                    Modifier le client
                </a>
                <form method="POST" action="{{ route('clients.destroy', $client) }}"
                      onsubmit="return confirm('Supprimer ce client et toutes ses données ?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-red-100 bg-red-50
                                   text-sm font-semibold text-red-600 hover:bg-red-100 transition-colors">
                        <i data-lucide="trash-2" style="width:15px;height:15px"></i>
                        Supprimer
                    </button>
                </form>
            </div>

        </div>

        {{-- ══════════ COLONNE DROITE ══════════ --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- ── Dernières ventes ─────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-primary"></i>
                        Dernières ventes
                    </h3>
                    <a href="{{ route('orders.create') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-50 text-primary text-xs font-semibold hover:bg-orange-100 transition-colors">
                        <i data-lucide="plus" style="width:13px;height:13px"></i>
                        Nouvelle vente
                    </a>
                </div>

                @forelse($client->orders as $order)
                <div class="px-5 py-3.5 border-b border-gray-50 last:border-0 flex items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('orders.show', $order) }}"
                               class="font-mono text-sm font-bold text-primary hover:underline">
                                {{ $order->reference }}
                            </a>
                            <span class="badge-status bg-{{ $order->getStatusColor() }}-50 text-{{ $order->getStatusColor() }}-700 text-xs">
                                {{ $order->getStatusLabel() }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $order->created_at->format('d/m/Y') }} •
                            {{ match($order->type) { 'tissu'=>'Tissu','pret_a_porter'=>'PAP',default=>'Mixte' } }}
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-bold text-dark">{{ number_format($order->total, 0, ',', ' ') }}</p>
                        <span class="text-xs {{ match($order->payment_status) { 'paid'=>'text-green-600','partial'=>'text-orange-500',default=>'text-red-500' } }} font-semibold">
                            {{ match($order->payment_status) { 'paid'=>'Payé','partial'=>'Partiel',default=>'Impayé' } }}
                        </span>
                    </div>
                    <a href="{{ route('orders.show', $order) }}"
                       class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark transition-colors shrink-0">
                        <i data-lucide="arrow-right" style="width:15px;height:15px"></i>
                    </a>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">
                    <i data-lucide="shopping-bag" class="w-8 h-8 mx-auto mb-2 text-gray-200"></i>
                    Aucune vente pour ce client
                </div>
                @endforelse
            </div>

            {{-- ── Commandes sur mesure ─────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                        <i data-lucide="scissors" class="w-4 h-4 text-purple-600"></i>
                        Commandes sur mesure
                    </h3>
                    <a href="{{ route('custom-orders.create') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-50 text-purple-700 text-xs font-semibold hover:bg-purple-100 transition-colors">
                        <i data-lucide="plus" style="width:13px;height:13px"></i>
                        Nouvelle commande
                    </a>
                </div>

                @forelse($client->customOrders as $co)
                <div class="px-5 py-3.5 border-b border-gray-50 last:border-0 flex items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('custom-orders.show', $co) }}"
                               class="font-mono text-sm font-bold text-purple-600 hover:underline">
                                {{ $co->reference }}
                            </a>
                            <span class="badge-status bg-{{ $co->getStatusColor() }}-50 text-{{ $co->getStatusColor() }}-700 text-xs">
                                {{ $co->getStatusLabel() }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $co->created_at->format('d/m/Y') }} •
                            {{ ucfirst($co->garment_type) }}
                            @if($co->delivery_date)
                                • Livraison : {{ $co->delivery_date->format('d/m/Y') }}
                            @endif
                        </p>
                        {{-- Barre de progression --}}
                        @if($co->status !== 'annule')
                        <div class="mt-1.5 flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                <div class="bg-purple-500 h-1.5 rounded-full" style="width: {{ $co->progress_percent }}%"></div>
                            </div>
                            <span class="text-xs text-gray-400 shrink-0">{{ $co->progress_percent }}%</span>
                        </div>
                        @endif
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-bold text-dark">{{ number_format($co->total, 0, ',', ' ') }}</p>
                        <p class="text-xs {{ $co->balance > 0 ? 'text-orange-500' : 'text-green-600' }} font-semibold">
                            {{ $co->balance > 0 ? 'Reste ' . number_format($co->balance, 0, ',', ' ') : 'Soldé' }}
                        </p>
                    </div>
                    <a href="{{ route('custom-orders.show', $co) }}"
                       class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark transition-colors shrink-0">
                        <i data-lucide="arrow-right" style="width:15px;height:15px"></i>
                    </a>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">
                    <i data-lucide="scissors" class="w-8 h-8 mx-auto mb-2 text-gray-200"></i>
                    Aucune commande sur mesure
                </div>
                @endforelse
            </div>

        </div>
    </div>
</div>
@endsection
