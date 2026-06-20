@extends('layouts.app')

@section('title', $maintenance->title)

@section('breadcrumb')
    <a href="{{ route('maintenance.index') }}" class="hover:text-primary-500 transition-colors">Interventions</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium truncate max-w-xs">{{ $maintenance->title }}</span>
@endsection

@push('styles')
<style>
    .info-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f9fafb;font-size:.875rem}
    .info-row:last-child{border-bottom:none}
    .info-label{font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#9ca3af}
    .info-value{font-weight:500;color:#1A1A2E;text-align:right;font-size:.875rem}
    .section-card{background:#fff;border:1px solid #f3f4f6;border-radius:20px;padding:24px;margin-bottom:16px}
    @keyframes fadeSlide{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
    .fade-in{animation:fadeSlide .22s ease both}
    .fade-in:nth-child(2){animation-delay:.06s}
    .fade-in:nth-child(3){animation-delay:.12s}
</style>
@endpush

@section('content')
@php
$typeCfg=[
    'preventive'=>['Préventive','shield-check','text-blue-600','bg-blue-50'],
    'corrective'=>['Corrective','wrench',      'text-amber-600','bg-amber-50'],
    'urgence'   =>['Urgence',   'zap',         'text-red-600',  'bg-red-50'],
];
$statusCfg=[
    'signale' =>['Signalé',  'bg-gray-100', 'text-gray-600', 'bg-gray-400'],
    'en_cours'=>['En cours', 'bg-amber-50', 'text-amber-700','bg-amber-400'],
    'resolu'  =>['Résolu',   'bg-green-50', 'text-green-700','bg-green-500'],
];
$tc = $typeCfg[$maintenance->type]     ?? ['Autre','tool','text-gray-500','bg-gray-50'];
$sc = $statusCfg[$maintenance->status] ?? ['—',    'bg-gray-100','text-gray-500','bg-gray-300'];
@endphp

{{-- Header --}}
<div class="flex items-start justify-between mb-6 fade-in">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl {{ $tc[3] }} flex items-center justify-center shrink-0">
            <i data-lucide="{{ $tc[1] }}" style="width:22px;height:22px" class="{{ $tc[2] }}"></i>
        </div>
        <div>
            <h2 class="text-xl font-bold text-dark font-display">{{ $maintenance->title }}</h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-sm text-gray-400">{{ $tc[0] }}</span>
                <span class="text-gray-200">·</span>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $sc[1] }} {{ $sc[2] }} px-2.5 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full {{ $sc[3] }}"></span>{{ $sc[0] }}
                </span>
            </div>
        </div>
    </div>
    <div class="flex gap-2 shrink-0">
        @if($maintenance->status !== 'resolu')
        {{-- Modal résolution --}}
        <button type="button"
                x-data @click="$dispatch('open-resolve')"
                class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <i data-lucide="check-circle" style="width:14px;height:14px"></i>Marquer résolu
        </button>
        @endif
        @if(auth()->user()->isAdmin())
        <form method="POST" action="{{ route('maintenance.destroy', $maintenance) }}"
              onsubmit="return confirm('Supprimer cette intervention ?')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 border border-gray-200 hover:border-red-200 hover:text-red-500 px-3 py-2 rounded-xl transition-colors">
                <i data-lucide="trash-2" style="width:14px;height:14px"></i>
            </button>
        </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Colonne latérale --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="section-card fade-in">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Informations</p>

            @if($maintenance->equipment)
            <div class="info-row">
                <span class="info-label">Équipement</span>
                <a href="{{ route('equipment.show', $maintenance->equipment) }}"
                   class="info-value text-primary-500 hover:underline">{{ $maintenance->equipment->name }}</a>
            </div>
            @endif

            @if($maintenance->reporter)
            <div class="info-row">
                <span class="info-label">Déclaré par</span>
                <span class="info-value">{{ $maintenance->reporter->name }}</span>
            </div>
            @endif

            @if($maintenance->scheduled_date)
            <div class="info-row">
                <span class="info-label">Date planifiée</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($maintenance->scheduled_date)->format('d/m/Y') }}</span>
            </div>
            @endif

            @if($maintenance->cost)
            <div class="info-row">
                <span class="info-label">Coût</span>
                <span class="info-value font-bold text-primary-500">{{ number_format($maintenance->cost,0,',',' ') }} FCFA</span>
            </div>
            @endif

            <div class="info-row">
                <span class="info-label">Signalé le</span>
                <span class="info-value">{{ $maintenance->created_at->format('d/m/Y H:i') }}</span>
            </div>

            @if($maintenance->resolved_at)
            <div class="info-row">
                <span class="info-label">Résolu le</span>
                <span class="info-value text-green-600">{{ \Carbon\Carbon::parse($maintenance->resolved_at)->format('d/m/Y H:i') }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Contenu principal --}}
    <div class="lg:col-span-2 space-y-4">

        <div class="section-card fade-in">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4 flex items-center gap-2">
                <i data-lucide="file-text" style="width:13px;height:13px"></i>Description
            </p>
            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $maintenance->description }}</p>
        </div>

        @if($maintenance->resolution)
        <div class="section-card fade-in" style="border-color:#d1fae5;background:#f0fdf4">
            <p class="text-xs font-bold uppercase tracking-wider text-green-600 mb-3 flex items-center gap-2">
                <i data-lucide="check-circle" style="width:13px;height:13px"></i>Résolution
            </p>
            <p class="text-sm text-green-800 leading-relaxed">{{ $maintenance->resolution }}</p>
        </div>
        @endif
    </div>
</div>

{{-- Modal résolution --}}
@if($maintenance->status !== 'resolu')
<div x-data="{ open: false }"
     @open-resolve.window="open = true"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none">
    <div class="absolute inset-0 bg-dark/50 backdrop-blur-sm" @click="open=false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 z-10"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-dark font-display">Résoudre l'intervention</h3>
            <button @click="open=false" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" style="width:18px;height:18px"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('maintenance.resolve', $maintenance) }}">
            @csrf @method('PUT')
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                    Résumé de la résolution
                </label>
                <textarea name="resolution" rows="4"
                          class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 bg-gray-50 resize-none"
                          placeholder="Décrivez les actions réalisées, pièces remplacées…"></textarea>
            </div>
            <div class="mb-5">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Coût (FCFA)</label>
                <div class="flex">
                    <input type="number" name="cost" min="0" step="100" placeholder="0"
                           class="flex-1 px-3 py-2.5 text-sm border border-gray-200 rounded-l-xl focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 bg-gray-50"
                           style="border-right:none">
                    <span class="px-3 flex items-center text-xs font-semibold text-gray-500 bg-gray-100 border border-gray-200 rounded-r-xl" style="border-left:none">FCFA</span>
                </div>
                <p class="text-xs text-gray-400 mt-1.5">L'équipement sera automatiquement remis en statut « Opérationnel ».</p>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" @click="open=false"
                        class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                    Annuler
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-semibold bg-green-600 hover:bg-green-700 text-white rounded-xl transition-colors">
                    Confirmer la résolution
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>document.addEventListener('DOMContentLoaded',()=>lucide.createIcons());</script>
@endpush
