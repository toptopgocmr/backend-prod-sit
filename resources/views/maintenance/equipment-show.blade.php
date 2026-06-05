@extends('layouts.app')

@section('title', $equipment->name)

@section('breadcrumb')
    <a href="{{ route('equipment.index') }}" class="hover:text-primary-500 transition-colors">Équipements</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">{{ $equipment->name }}</span>
@endsection

@push('styles')
<style>
    .info-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f9fafb;font-size:.875rem}
    .info-row:last-child{border-bottom:none}
    .info-label{font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#9ca3af}
    .info-value{font-weight:500;color:#1A1A2E;text-align:right}
    .section-card{background:#fff;border:1px solid #f3f4f6;border-radius:20px;padding:24px;margin-bottom:16px}
    .log-item{display:flex;gap:12px;padding:12px 0;border-bottom:1px solid #f9fafb}
    .log-item:last-child{border-bottom:none}
    .status-pill{display:inline-flex;align-items:center;gap:5px;font-size:.7rem;font-weight:600;padding:3px 10px;border-radius:9999px}
    @keyframes fadeSlide{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
    .fade-in{animation:fadeSlide .22s ease both}
    .fade-in:nth-child(2){animation-delay:.06s}
    .fade-in:nth-child(3){animation-delay:.12s}
</style>
@endpush

@section('content')
@php
$typeLabels=['machine_a_coudre'=>['Machine à coudre','scissors','text-purple-600','bg-purple-50'],'climatiseur'=>['Climatiseur','wind','text-blue-600','bg-blue-50'],'groupe_electrogene'=>['Groupe électrogène','zap','text-amber-600','bg-amber-50'],'ordinateur'=>['Ordinateur','monitor','text-gray-600','bg-gray-100'],'autre'=>['Autre','package','text-gray-500','bg-gray-50']];
$statusCfg=['operationnel'=>['label'=>'Opérationnel','dot'=>'bg-green-400','bg'=>'bg-green-50','text'=>'text-green-700'],'en_panne'=>['label'=>'En panne','dot'=>'bg-red-400','bg'=>'bg-red-50','text'=>'text-red-700'],'en_maintenance'=>['label'=>'En maintenance','dot'=>'bg-amber-400','bg'=>'bg-amber-50','text'=>'text-amber-700']];
$type=$typeLabels[$equipment->type]??['Autre','package','text-gray-500','bg-gray-50'];
$status=$statusCfg[$equipment->status]??['label'=>$equipment->status,'dot'=>'bg-gray-300','bg'=>'bg-gray-50','text'=>'text-gray-500'];
$overdue=method_exists($equipment,'isOverdue')&&$equipment->isOverdue();
@endphp

{{-- Header --}}
<div class="flex items-start justify-between mb-6 fade-in">
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl {{ $type[3] }} flex items-center justify-center shrink-0">
            <i data-lucide="{{ $type[1] }}" style="width:24px;height:24px" class="{{ $type[2] }}"></i>
        </div>
        <div>
            <h2 class="text-xl font-bold text-dark font-display">{{ $equipment->name }}</h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-sm text-gray-400">{{ $type[0] }}</span>
                <span class="text-gray-200">·</span>
                <span class="status-pill {{ $status['bg'] }} {{ $status['text'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
                    {{ $status['label'] }}
                </span>
                @if($overdue)
                <span class="inline-flex items-center gap-1 text-xs font-semibold bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full">
                    <i data-lucide="alert-triangle" style="width:10px;height:10px"></i>Maintenance en retard
                </span>
                @endif
            </div>
        </div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('equipment.edit', $equipment) }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 border border-gray-200 hover:border-gray-300 px-3 py-2 rounded-xl transition-colors">
            <i data-lucide="edit-3" style="width:14px;height:14px"></i>Modifier
        </a>
        <a href="{{ route('maintenance.create', ['equipment_id'=>$equipment->id]) }}"
           class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <i data-lucide="plus" style="width:14px;height:14px"></i>Intervention
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Colonne gauche --}}
    <div class="lg:col-span-1 space-y-4">

        {{-- Fiche --}}
        <div class="section-card fade-in">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Fiche équipement</p>
            @if($equipment->brand)
            <div class="info-row">
                <span class="info-label">Marque</span>
                <span class="info-value">{{ $equipment->brand }}</span>
            </div>
            @endif
            @if($equipment->location)
            <div class="info-row">
                <span class="info-label">Emplacement</span>
                <span class="info-value">{{ $equipment->location }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Intervalle</span>
                <span class="info-value">{{ $equipment->maintenance_interval_days }} jours</span>
            </div>
            <div class="info-row">
                <span class="info-label">Interventions</span>
                <span class="info-value font-bold text-primary-500">{{ $equipment->maintenanceLogs->count() }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Enregistré le</span>
                <span class="info-value">{{ $equipment->created_at->format('d/m/Y') }}</span>
            </div>
        </div>

        {{-- Notes --}}
        @if($equipment->notes)
        <div class="section-card fade-in">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2">
                <i data-lucide="file-text" style="width:13px;height:13px"></i>Notes
            </p>
            <p class="text-sm text-gray-600 leading-relaxed">{{ $equipment->notes }}</p>
        </div>
        @endif
    </div>

    {{-- Historique maintenance --}}
    <div class="lg:col-span-2 fade-in">
        <div class="section-card h-full">
            <div class="flex items-center justify-between mb-4">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 flex items-center gap-2">
                    <i data-lucide="history" style="width:13px;height:13px"></i>Historique des interventions
                </p>
                <a href="{{ route('maintenance.create', ['equipment_id'=>$equipment->id]) }}"
                   class="inline-flex items-center gap-1 text-xs font-semibold text-primary-500 hover:text-primary-600">
                    <i data-lucide="plus" style="width:12px;height:12px"></i>Nouvelle
                </a>
            </div>

            @if($equipment->maintenanceLogs->isEmpty())
                <div class="text-center py-10">
                    <i data-lucide="clipboard-list" style="width:28px;height:28px;color:#d1d5db;margin:0 auto 12px"></i>
                    <p class="text-sm text-gray-400">Aucune intervention enregistrée</p>
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($equipment->maintenanceLogs->sortByDesc('created_at') as $log)
                    <div class="log-item">
                        <div class="w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="wrench" style="width:14px;height:14px;color:#E8820C"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-semibold text-dark truncate">{{ $log->description ?? 'Intervention de maintenance' }}</p>
                                <span class="text-xs text-gray-400 shrink-0">{{ $log->created_at->format('d/m/Y') }}</span>
                            </div>
                            @if($log->reporter)
                                <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                                    <i data-lucide="user" style="width:10px;height:10px"></i>{{ $log->reporter->name }}
                                </p>
                            @endif
                            @if($log->cost)
                                <p class="text-xs font-semibold text-primary-500 mt-0.5">
                                    {{ number_format($log->cost,0,',',' ') }} FCFA
                                </p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>document.addEventListener('DOMContentLoaded',()=>lucide.createIcons());</script>
@endpush
