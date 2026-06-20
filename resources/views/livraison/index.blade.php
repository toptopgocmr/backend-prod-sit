@extends('layouts.app')

@section('title', 'Livraisons')

@section('breadcrumb')
    <span>Opérations</span>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Livraisons</span>
@endsection

@push('styles')
<style>
    .stat-card { transition: transform .18s ease, box-shadow .18s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(26,26,46,.1); }
    .ref-badge { font-family: 'Courier New', monospace; }
    .status-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
    .row-hover { transition: background .12s; }
    .row-hover:hover { background: #FEF3E2; }
    .action-icon { transition: color .12s, background .12s; }
    .action-icon:hover { color: #E8820C; background: #FEF3E2; border-radius: 6px; }
    @keyframes fadeSlide { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:none; } }
    .fade-in { animation: fadeSlide .22s ease both; }
    .fade-in:nth-child(2){animation-delay:.05s}
    .fade-in:nth-child(3){animation-delay:.1s}
    .fade-in:nth-child(4){animation-delay:.15s}
</style>
@endpush

@section('content')

@php
    $statusConfig = [
        'pending'    => ['label'=>'En attente',  'dot'=>'bg-gray-400',   'bg'=>'bg-gray-100',   'text'=>'text-gray-600'],
        'assigned'   => ['label'=>'Assignée',    'dot'=>'bg-blue-400',   'bg'=>'bg-blue-50',    'text'=>'text-blue-700'],
        'in_transit' => ['label'=>'En transit',  'dot'=>'bg-amber-400',  'bg'=>'bg-amber-50',   'text'=>'text-amber-700'],
        'delivered'  => ['label'=>'Livrée',      'dot'=>'bg-green-500',  'bg'=>'bg-green-50',   'text'=>'text-green-700'],
        'failed'     => ['label'=>'Échouée',     'dot'=>'bg-red-400',    'bg'=>'bg-red-50',     'text'=>'text-red-700'],
        'returned'   => ['label'=>'Retournée',   'dot'=>'bg-pink-400',   'bg'=>'bg-pink-50',    'text'=>'text-pink-700'],
    ];

    $total      = $deliveries->total();
    $delivered  = \App\Models\Delivery::where('status','delivered')->count();
    $inProgress = \App\Models\Delivery::whereIn('status',['assigned','in_transit'])->count();
    $failed     = \App\Models\Delivery::whereIn('status',['failed','returned'])->count();
@endphp

{{-- ── En-tête page ──────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between mb-6 fade-in">
    <div>
        <h2 class="text-xl font-bold text-dark font-display">Toutes les livraisons</h2>
        <p class="text-sm text-gray-400 mt-0.5">Suivi et gestion des expéditions</p>
    </div>
    <a href="{{ route('deliveries.create') }}"
       class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-sm shadow-primary-500/30">
        <i data-lucide="plus" style="width:16px;height:16px"></i>
        Nouvelle livraison
    </a>
</div>

{{-- ── Cartes stats ───────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-4 fade-in">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total</span>
            <span class="w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center">
                <i data-lucide="package" style="width:16px;height:16px;color:#E8820C"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-dark font-display">{{ $total }}</p>
        <p class="text-xs text-gray-400 mt-1">Ce mois-ci</p>
    </div>

    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-4 fade-in">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Livrées</span>
            <span class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                <i data-lucide="check-circle" style="width:16px;height:16px;color:#16a34a"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-dark font-display">{{ $delivered }}</p>
        <p class="text-xs text-gray-400 mt-1">
            @if($total > 0)
                {{ round($delivered / $total * 100) }}% de succès
            @else
                —
            @endif
        </p>
    </div>

    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-4 fade-in">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">En cours</span>
            <span class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                <i data-lucide="truck" style="width:16px;height:16px;color:#d97706"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-dark font-display">{{ $inProgress }}</p>
        <p class="text-xs text-gray-400 mt-1">Assignées + transit</p>
    </div>

    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-4 fade-in">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Échouées</span>
            <span class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                <i data-lucide="x-circle" style="width:16px;height:16px;color:#dc2626"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-dark font-display">{{ $failed }}</p>
        <p class="text-xs text-gray-400 mt-1">À retraiter</p>
    </div>
</div>

{{-- ── Filtres ─────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 p-4 mb-4 fade-in">
    <form method="GET" action="{{ route('deliveries.index') }}"
          class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-40">
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Statut</label>
            <div class="relative">
                <i data-lucide="filter" style="width:14px;height:14px;position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af"></i>
                <select name="status"
                        class="w-full pl-8 pr-3 py-2 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 appearance-none">
                    <option value="">Tous les statuts</option>
                    @foreach($statusConfig as $val => $cfg)
                        <option value="{{ $val }}" @selected(request('status') === $val)>{{ $cfg['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                <i data-lucide="search" style="width:14px;height:14px"></i>
                Filtrer
            </button>
            @if(request('status'))
                <a href="{{ route('deliveries.index') }}"
                   class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 px-3 py-2 rounded-xl transition-colors">
                    <i data-lucide="x" style="width:14px;height:14px"></i>
                    Réinitialiser
                </a>
            @endif
        </div>

        <div class="ml-auto text-xs text-gray-400 self-center">
            {{ $deliveries->total() }} livraison{{ $deliveries->total() > 1 ? 's' : '' }}
        </div>
    </form>
</div>

{{-- ── Tableau ──────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 overflow-hidden fade-in">

    @if($deliveries->isEmpty())
        <div class="text-center py-16">
            <div class="w-16 h-16 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="package-open" style="width:28px;height:28px;color:#d1d5db"></i>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-1">Aucune livraison trouvée</p>
            <p class="text-xs text-gray-400 mb-5">
                @if(request('status'))
                    Aucun résultat pour ce filtre.
                @else
                    Commencez par créer votre première livraison.
                @endif
            </p>
            <a href="{{ route('deliveries.create') }}"
               class="inline-flex items-center gap-2 bg-primary-500 text-white text-sm font-semibold px-4 py-2 rounded-xl">
                <i data-lucide="plus" style="width:14px;height:14px"></i>
                Créer une livraison
            </a>
        </div>

    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Référence</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Client</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Livreur</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Frais</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($deliveries as $delivery)
                    @php $cfg = $statusConfig[$delivery->status] ?? ['label'=>$delivery->status,'dot'=>'bg-gray-300','bg'=>'bg-gray-50','text'=>'text-gray-500']; @endphp
                    <tr class="row-hover">
                        {{-- Référence --}}
                        <td class="px-5 py-4">
                            <span class="ref-badge text-xs font-semibold text-primary-500 bg-primary-50 px-2.5 py-1 rounded-lg whitespace-nowrap">
                                {{ $delivery->reference }}
                            </span>
                        </td>

                        {{-- Client --}}
                        <td class="px-4 py-4">
                            @if($delivery->client)
                                <p class="font-semibold text-dark">{{ $delivery->client->first_name }} {{ $delivery->client->last_name }}</p>
                                @if($delivery->client->phone)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $delivery->client->phone }}</p>
                                @endif
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- Type --}}
                        <td class="px-4 py-4">
                            @if($delivery->type === 'livraison')
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full">
                                    <i data-lucide="truck" style="width:11px;height:11px"></i>
                                    Livraison
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-amber-50 text-amber-700 px-2.5 py-1 rounded-full">
                                    <i data-lucide="store" style="width:11px;height:11px"></i>
                                    Retrait
                                </span>
                            @endif
                        </td>

                        {{-- Livreur --}}
                        <td class="px-4 py-4">
                            @if($delivery->driver)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-primary-50 flex items-center justify-center text-xs font-bold text-primary-500 shrink-0">
                                        {{ strtoupper(substr($delivery->driver->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-dark text-sm">{{ $delivery->driver->name }}</span>
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic">Non assigné</span>
                            @endif
                        </td>

                        {{-- Statut --}}
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $cfg['bg'] }} {{ $cfg['text'] }} px-2.5 py-1 rounded-full">
                                <span class="status-dot {{ $cfg['dot'] }}"></span>
                                {{ $cfg['label'] }}
                            </span>
                        </td>

                        {{-- Frais --}}
                        <td class="px-4 py-4">
                            @if($delivery->delivery_fee)
                                <span class="font-mono text-sm font-semibold text-dark">
                                    {{ number_format($delivery->delivery_fee, 0, ',', ' ') }}
                                    <span class="text-xs font-normal text-gray-400">FCFA</span>
                                </span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- Date --}}
                        <td class="px-4 py-4">
                            <span class="text-xs text-gray-400">{{ $delivery->created_at->format('d/m/Y') }}</span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-1">

                                {{-- Voir --}}
                                <a href="{{ route('deliveries.show', $delivery) }}"
                                   class="action-icon p-1.5 text-gray-400 rounded-lg" title="Voir le détail">
                                    <i data-lucide="eye" style="width:15px;height:15px"></i>
                                </a>

                                {{-- Assigner --}}
                                @if($drivers->isNotEmpty())
                                <button type="button"
                                        class="action-icon p-1.5 text-gray-400 rounded-lg"
                                        title="Assigner un livreur"
                                        x-data
                                        @click="$dispatch('open-assign-{{ $delivery->id }}')">
                                    <i data-lucide="user-plus" style="width:15px;height:15px"></i>
                                </button>
                                @endif

                                {{-- Statut --}}
                                <button type="button"
                                        class="action-icon p-1.5 text-gray-400 rounded-lg"
                                        title="Changer le statut"
                                        x-data
                                        @click="$dispatch('open-status-{{ $delivery->id }}')">
                                    <i data-lucide="refresh-cw" style="width:15px;height:15px"></i>
                                </button>

                                {{-- Supprimer --}}
                                @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('deliveries.destroy', $delivery) }}"
                                      x-data
                                      @submit.prevent="if(confirm('Supprimer cette livraison ?')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-icon p-1.5 text-gray-400 hover:!text-red-500 hover:!bg-red-50 rounded-lg" title="Supprimer">
                                        <i data-lucide="trash-2" style="width:15px;height:15px"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($deliveries->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-50">
            <p class="text-xs text-gray-400">
                {{ $deliveries->firstItem() }}–{{ $deliveries->lastItem() }}
                sur <span class="font-semibold text-gray-600">{{ $deliveries->total() }}</span> livraisons
            </p>
            <div class="flex gap-1">
                {{ $deliveries->links() }}
            </div>
        </div>
        @endif
    @endif
</div>

{{-- ── Modals Alpine ─────────────────────────────────────────────────────── --}}
@foreach($deliveries as $delivery)

{{-- Modal assigner livreur --}}
@if($drivers->isNotEmpty())
<div x-data="{ open: false }"
     @open-assign-{{ $delivery->id }}.window="open = true"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none">
    <div class="absolute inset-0 bg-dark/50 backdrop-blur-sm" @click="open=false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 z-10"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-dark font-display">Assigner un livreur</h3>
            <button @click="open=false" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" style="width:18px;height:18px"></i>
            </button>
        </div>
        <p class="text-xs text-gray-400 mb-4">
            Livraison <span class="font-mono font-semibold text-primary-500">{{ $delivery->reference }}</span>
        </p>
        <form method="POST" action="{{ route('deliveries.assign', $delivery) }}">
            @csrf @method('PUT')
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Livreur</label>
                <select name="driver_id" required
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-gray-50">
                    <option value="">Choisir…</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" @selected($delivery->driver_id == $driver->id)>
                            {{ $driver->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" @click="open=false"
                        class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                    Annuler
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-semibold bg-primary-500 hover:bg-primary-600 text-white rounded-xl transition-colors">
                    Assigner
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Modal changer statut --}}
<div x-data="{ open: false }"
     @open-status-{{ $delivery->id }}.window="open = true"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none">
    <div class="absolute inset-0 bg-dark/50 backdrop-blur-sm" @click="open=false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 z-10"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-dark font-display">Changer le statut</h3>
            <button @click="open=false" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" style="width:18px;height:18px"></i>
            </button>
        </div>
        <p class="text-xs text-gray-400 mb-4">
            Livraison <span class="font-mono font-semibold text-primary-500">{{ $delivery->reference }}</span>
        </p>
        <form method="POST" action="{{ route('deliveries.status', $delivery) }}">
            @csrf @method('PUT')
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Nouveau statut</label>
                <select name="status" required
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-gray-50">
                    @foreach($statusConfig as $val => $cfg)
                        <option value="{{ $val }}" @selected($delivery->status === $val)>{{ $cfg['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" @click="open=false"
                        class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                    Annuler
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-semibold bg-primary-500 hover:bg-primary-600 text-white rounded-xl transition-colors">
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>

@endforeach

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>
@endpush
