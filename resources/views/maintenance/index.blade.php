@extends('layouts.app')

@section('title', 'Interventions')

@section('breadcrumb')
    <span>Maintenance</span>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Interventions</span>
@endsection

@push('styles')
<style>
    .stat-card{transition:transform .18s ease,box-shadow .18s ease}
    .stat-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(26,26,46,.1)}
    .row-hover{transition:background .12s}
    .row-hover:hover{background:#FEF3E2}
    .action-icon{transition:color .12s,background .12s;border-radius:8px;padding:6px;display:inline-flex}
    .action-icon:hover{color:#E8820C;background:#FEF3E2}
    @keyframes fadeSlide{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
    .fade-in{animation:fadeSlide .22s ease both}
    .fade-in:nth-child(2){animation-delay:.05s}
    .fade-in:nth-child(3){animation-delay:.1s}
    .fade-in:nth-child(4){animation-delay:.15s}
    .fade-in:nth-child(5){animation-delay:.2s}
</style>
@endpush

@section('content')

@php
$typeCfg=[
    'preventive'=>['Préventive','bg-blue-50','text-blue-700','bg-blue-400'],
    'corrective'=>['Corrective','bg-amber-50','text-amber-700','bg-amber-400'],
    'urgence'   =>['Urgence',   'bg-red-50',  'text-red-700',  'bg-red-400'],
];
$statusCfg=[
    'signale' =>['Signalé',   'bg-gray-100', 'text-gray-600', 'bg-gray-400'],
    'en_cours'=>['En cours',  'bg-amber-50', 'text-amber-700','bg-amber-400'],
    'resolu'  =>['Résolu',    'bg-green-50', 'text-green-700','bg-green-500'],
];

$total    = $logs->total();
$enCours  = $logs->getCollection()->whereIn('status',['signale','en_cours'])->count();
$resolus  = $logs->getCollection()->where('status','resolu')->count();
$urgences = $logs->getCollection()->where('type','urgence')->count();
@endphp

{{-- En-tête --}}
<div class="flex items-start justify-between mb-6 fade-in">
    <div>
        <h2 class="text-xl font-bold text-dark font-display">Interventions</h2>
        <p class="text-sm text-gray-400 mt-0.5">Suivi des interventions de maintenance</p>
    </div>
    <a href="{{ route('maintenance.create') }}"
       class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-sm shadow-primary-500/30">
        <i data-lucide="plus" style="width:16px;height:16px"></i>
        Nouvelle intervention
    </a>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-4 fade-in">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total</span>
            <span class="w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center">
                <i data-lucide="clipboard-list" style="width:16px;height:16px;color:#E8820C"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-dark font-display">{{ $total }}</p>
        <p class="text-xs text-gray-400 mt-1">Enregistrées</p>
    </div>
    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-4 fade-in">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">En cours</span>
            <span class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                <i data-lucide="clock" style="width:16px;height:16px;color:#d97706"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-dark font-display">{{ $enCours }}</p>
        <p class="text-xs text-gray-400 mt-1">Signalées + en cours</p>
    </div>
    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-4 fade-in">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Résolues</span>
            <span class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                <i data-lucide="check-circle" style="width:16px;height:16px;color:#16a34a"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-dark font-display">{{ $resolus }}</p>
        <p class="text-xs text-gray-400 mt-1">Sur cette page</p>
    </div>
    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-4 fade-in">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Urgences</span>
            <span class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                <i data-lucide="alert-triangle" style="width:16px;height:16px;color:#dc2626"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-dark font-display">{{ $urgences }}</p>
        <p class="text-xs text-gray-400 mt-1">Priorité critique</p>
    </div>
</div>

{{-- Tableau --}}
<div class="bg-white rounded-2xl border border-gray-100 overflow-hidden fade-in">

    @if($logs->isEmpty())
        <div class="text-center py-16">
            <div class="w-16 h-16 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="clipboard-list" style="width:28px;height:28px;color:#d1d5db"></i>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-1">Aucune intervention enregistrée</p>
            <p class="text-xs text-gray-400 mb-5">Créez votre première intervention de maintenance.</p>
            <a href="{{ route('maintenance.create') }}"
               class="inline-flex items-center gap-2 bg-primary-500 text-white text-sm font-semibold px-4 py-2 rounded-xl">
                <i data-lucide="plus" style="width:14px;height:14px"></i>Nouvelle intervention
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Équipement</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Titre</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Déclaré par</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($logs as $log)
                    @php
                        $tc = $typeCfg[$log->type]     ?? ['Autre',  'bg-gray-100','text-gray-500','bg-gray-300'];
                        $sc = $statusCfg[$log->status] ?? ['—',      'bg-gray-100','text-gray-500','bg-gray-300'];
                    @endphp
                    <tr class="row-hover">
                        <td class="px-5 py-4">
                            @if($log->equipment)
                                <p class="font-semibold text-dark">{{ $log->equipment->name }}</p>
                                @if($log->equipment->location)
                                    <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                                        <i data-lucide="map-pin" style="width:10px;height:10px"></i>{{ $log->equipment->location }}
                                    </p>
                                @endif
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 max-w-xs">
                            <p class="text-sm font-medium text-dark truncate">{{ $log->title }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $tc[1] }} {{ $tc[2] }} px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full {{ $tc[3] }}"></span>
                                {{ $tc[0] }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            @if($log->reporter)
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-primary-50 flex items-center justify-center text-xs font-bold text-primary-500 shrink-0">
                                        {{ strtoupper(substr($log->reporter->name,0,1)) }}
                                    </div>
                                    <span class="text-sm text-dark">{{ $log->reporter->name }}</span>
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $sc[1] }} {{ $sc[2] }} px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sc[3] }}"></span>
                                {{ $sc[0] }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-xs text-gray-400">{{ $log->created_at->format('d/m/Y') }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('maintenance.show', $log) }}"
                                   class="action-icon text-gray-400" title="Voir">
                                    <i data-lucide="eye" style="width:15px;height:15px"></i>
                                </a>
                                @if($log->status !== 'resolu')
                                <form method="POST" action="{{ route('maintenance.resolve', $log) }}">
                                    @csrf @method('PUT')
                                    <button type="submit"
                                            class="action-icon text-gray-400 hover:!text-green-600 hover:!bg-green-50"
                                            title="Marquer résolu"
                                            onclick="return confirm('Marquer cette intervention comme résolue ?')">
                                        <i data-lucide="check" style="width:15px;height:15px"></i>
                                    </button>
                                </form>
                                @endif
                                @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('maintenance.destroy', $log) }}"
                                      onsubmit="return confirm('Supprimer cette intervention ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="action-icon text-gray-400 hover:!text-red-500 hover:!bg-red-50"
                                            title="Supprimer">
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

        @if($logs->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-50">
            <p class="text-xs text-gray-400">
                {{ $logs->firstItem() }}–{{ $logs->lastItem() }}
                sur <span class="font-semibold text-gray-600">{{ $logs->total() }}</span> interventions
            </p>
            {{ $logs->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
@push('scripts')
<script>document.addEventListener('DOMContentLoaded',()=>lucide.createIcons());</script>
@endpush
