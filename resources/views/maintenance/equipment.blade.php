@extends('layouts.app')

@section('title', 'Équipements')

@section('breadcrumb')
    <span>Maintenance</span>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Équipements</span>
@endsection

@push('styles')
<style>
    .stat-card { transition: transform .18s ease, box-shadow .18s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(26,26,46,.1); }
    .eq-card { background:#fff; border:1px solid #f3f4f6; border-radius:16px; padding:20px; transition: border-color .15s, box-shadow .15s; }
    .eq-card:hover { border-color:#fde0b4; box-shadow: 0 4px 20px rgba(232,130,12,.08); }
    .status-pill { display:inline-flex; align-items:center; gap:5px; font-size:.7rem; font-weight:600; padding:3px 10px; border-radius:9999px; }
    .action-icon { transition: color .12s, background .12s; border-radius:8px; padding:6px; display:inline-flex; }
    .action-icon:hover { color:#E8820C; background:#FEF3E2; }
    @keyframes fadeSlide { from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none} }
    .fade-in { animation: fadeSlide .22s ease both; }
    .fade-in:nth-child(2){animation-delay:.05s}
    .fade-in:nth-child(3){animation-delay:.1s}
    .fade-in:nth-child(4){animation-delay:.15s}
    .fade-in:nth-child(5){animation-delay:.2s}
    .progress-bar { height:4px; border-radius:2px; background:#f3f4f6; overflow:hidden; margin-top:8px; }
    .progress-fill { height:100%; border-radius:2px; transition:width .4s ease; }
</style>
@endpush

@section('content')

@php
$typeLabels = [
    'machine_a_coudre'   => ['label'=>'Machine à coudre', 'icon'=>'scissors',     'color'=>'text-purple-600', 'bg'=>'bg-purple-50'],
    'climatiseur'        => ['label'=>'Climatiseur',       'icon'=>'wind',         'color'=>'text-blue-600',   'bg'=>'bg-blue-50'],
    'groupe_electrogene' => ['label'=>'Groupe électrogène','icon'=>'zap',          'color'=>'text-amber-600',  'bg'=>'bg-amber-50'],
    'ordinateur'         => ['label'=>'Ordinateur',        'icon'=>'monitor',      'color'=>'text-gray-600',   'bg'=>'bg-gray-100'],
    'autre'              => ['label'=>'Autre',             'icon'=>'package',      'color'=>'text-gray-500',   'bg'=>'bg-gray-50'],
];
$statusCfg = [
    'operationnel' => ['label'=>'Opérationnel', 'dot'=>'bg-green-400',  'bg'=>'bg-green-50',  'text'=>'text-green-700'],
    'en_panne'     => ['label'=>'En panne',     'dot'=>'bg-red-400',    'bg'=>'bg-red-50',    'text'=>'text-red-700'],
    'en_maintenance'=> ['label'=>'Maintenance', 'dot'=>'bg-amber-400',  'bg'=>'bg-amber-50',  'text'=>'text-amber-700'],
];
@endphp

{{-- En-tête --}}
<div class="flex items-start justify-between mb-6 fade-in">
    <div>
        <h2 class="text-xl font-bold text-dark font-display">Équipements</h2>
        <p class="text-sm text-gray-400 mt-0.5">Suivi du parc matériel et maintenance préventive</p>
    </div>
    <a href="{{ route('equipment.create') }}"
       class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-sm shadow-primary-500/30">
        <i data-lucide="plus" style="width:16px;height:16px"></i>
        Ajouter un équipement
    </a>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-4 fade-in">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total</span>
            <span class="w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center">
                <i data-lucide="tool" style="width:16px;height:16px;color:#E8820C"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-dark font-display">{{ $stats['total'] }}</p>
        <p class="text-xs text-gray-400 mt-1">Équipements enregistrés</p>
    </div>

    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-4 fade-in">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Opérationnels</span>
            <span class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                <i data-lucide="check-circle" style="width:16px;height:16px;color:#16a34a"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-dark font-display">{{ $stats['operationnel'] }}</p>
        <div class="progress-bar">
            <div class="progress-fill bg-green-400"
                 style="width:{{ $stats['total'] > 0 ? round($stats['operationnel']/$stats['total']*100) : 0 }}%"></div>
        </div>
    </div>

    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-4 fade-in">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">En panne</span>
            <span class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                <i data-lucide="alert-triangle" style="width:16px;height:16px;color:#dc2626"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-dark font-display">{{ $stats['en_panne'] }}</p>
        <p class="text-xs text-gray-400 mt-1">Nécessitent intervention</p>
    </div>

    <div class="stat-card bg-white rounded-2xl border border-gray-100 p-4 fade-in">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">En retard</span>
            <span class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                <i data-lucide="clock" style="width:16px;height:16px;color:#d97706"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-dark font-display">{{ $stats['overdue'] }}</p>
        <p class="text-xs text-gray-400 mt-1">Maintenance dépassée</p>
    </div>
</div>

{{-- Grille équipements --}}
@if($equipment->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 text-center py-16 fade-in">
        <div class="w-16 h-16 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="tool" style="width:28px;height:28px;color:#d1d5db"></i>
        </div>
        <p class="text-sm font-medium text-gray-500 mb-1">Aucun équipement enregistré</p>
        <p class="text-xs text-gray-400 mb-5">Ajoutez votre premier équipement pour commencer le suivi.</p>
        <a href="{{ route('equipment.create') }}"
           class="inline-flex items-center gap-2 bg-primary-500 text-white text-sm font-semibold px-4 py-2 rounded-xl">
            <i data-lucide="plus" style="width:14px;height:14px"></i>
            Ajouter un équipement
        </a>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($equipment as $eq)
        @php
            $type   = $typeLabels[$eq->type]   ?? ['label'=>$eq->type,'icon'=>'package','color'=>'text-gray-500','bg'=>'bg-gray-50'];
            $status = $statusCfg[$eq->status]  ?? ['label'=>$eq->status,'dot'=>'bg-gray-300','bg'=>'bg-gray-50','text'=>'text-gray-500'];
            $overdue = method_exists($eq,'isOverdue') && $eq->isOverdue();
        @endphp
        <div class="eq-card fade-in {{ $overdue ? 'border-amber-200' : '' }}">

            {{-- Header --}}
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $type['bg'] }} flex items-center justify-center shrink-0">
                        <i data-lucide="{{ $type['icon'] }}" style="width:18px;height:18px" class="{{ $type['color'] }}"></i>
                    </div>
                    <div>
                        <p class="font-bold text-dark text-sm leading-tight">{{ $eq->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $type['label'] }}</p>
                    </div>
                </div>
                <span class="status-pill {{ $status['bg'] }} {{ $status['text'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
                    {{ $status['label'] }}
                </span>
            </div>

            {{-- Infos --}}
            <div class="space-y-2 mb-4">
                @if($eq->brand)
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <i data-lucide="tag" style="width:12px;height:12px;color:#9ca3af"></i>
                    <span>{{ $eq->brand }}</span>
                </div>
                @endif
                @if($eq->location)
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <i data-lucide="map-pin" style="width:12px;height:12px;color:#9ca3af"></i>
                    <span>{{ $eq->location }}</span>
                </div>
                @endif
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <i data-lucide="wrench" style="width:12px;height:12px;color:#9ca3af"></i>
                    <span>{{ $eq->maintenance_logs_count }} intervention{{ $eq->maintenance_logs_count > 1 ? 's' : '' }}</span>
                </div>
                <div class="flex items-center gap-2 text-xs {{ $overdue ? 'text-amber-600 font-semibold' : 'text-gray-500' }}">
                    <i data-lucide="clock" style="width:12px;height:12px;color:{{ $overdue ? '#d97706' : '#9ca3af' }}"></i>
                    <span>Intervalle : {{ $eq->maintenance_interval_days }} j</span>
                    @if($overdue)
                        <span class="ml-auto inline-flex items-center gap-1 text-xs font-semibold bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full">
                            <i data-lucide="alert-triangle" style="width:10px;height:10px"></i>
                            En retard
                        </span>
                    @endif
                </div>
            </div>

            {{-- Séparateur --}}
            <div class="border-t border-gray-50 pt-3 flex items-center justify-between">
                <div class="flex gap-1">
                    <a href="{{ route('equipment.show', $eq) }}"
                       class="action-icon text-gray-400" title="Voir le détail">
                        <i data-lucide="eye" style="width:15px;height:15px"></i>
                    </a>
                    <a href="{{ route('equipment.edit', $eq) }}"
                       class="action-icon text-gray-400" title="Modifier">
                        <i data-lucide="edit-3" style="width:15px;height:15px"></i>
                    </a>
                    @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('equipment.destroy', $eq) }}"
                          onsubmit="return confirm('Supprimer cet équipement ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-icon text-gray-400 hover:!text-red-500 hover:!bg-red-50" title="Supprimer">
                            <i data-lucide="trash-2" style="width:15px;height:15px"></i>
                        </button>
                    </form>
                    @endif
                </div>
                <a href="{{ route('maintenance.create', ['equipment_id'=>$eq->id]) }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-500 hover:text-primary-600 bg-primary-50 hover:bg-primary-100 px-3 py-1.5 rounded-lg transition-colors">
                    <i data-lucide="plus" style="width:12px;height:12px"></i>
                    Intervention
                </a>
            </div>
        </div>
        @endforeach
    </div>
@endif

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>
@endpush
