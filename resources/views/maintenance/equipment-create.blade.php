@extends('layouts.app')

@section('title', 'Ajouter un équipement')

@section('breadcrumb')
    <a href="{{ route('equipment.index') }}" class="hover:text-primary-500 transition-colors">Équipements</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Ajouter</span>
@endsection

@push('styles')
<style>
    .form-input{width:100%;padding:10px 14px;font-size:.875rem;border:1.5px solid #e5e7eb;border-radius:12px;background:#f9fafb;color:#1A1A2E;font-family:'Plus Jakarta Sans',sans-serif;transition:border-color .15s,box-shadow .15s,background .15s;outline:none}
    .form-input:focus{border-color:#E8820C;background:#fff;box-shadow:0 0 0 3px #E8820C22}
    .form-input::placeholder{color:#9ca3af}
    .form-label{display:block;font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin-bottom:6px}
    .section-card{background:#fff;border:1px solid #f3f4f6;border-radius:20px;padding:24px;margin-bottom:16px}
    .section-title{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:18px;display:flex;align-items:center;gap:8px}
    .section-title::after{content:'';flex:1;height:1px;background:#f3f4f6}
    .type-option{flex:1;border:1.5px solid #e5e7eb;border-radius:12px;padding:12px;cursor:pointer;transition:border-color .15s,background .15s;background:#f9fafb;display:flex;align-items:center;gap:10px}
    .type-option:has(input:checked){border-color:#E8820C;background:#FEF3E2}
    .type-option input{position:absolute;opacity:0}
    @keyframes fadeSlide{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
    .fade-in{animation:fadeSlide .22s ease both}
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="flex items-center justify-between mb-6 fade-in">
        <div>
            <h2 class="text-xl font-bold text-dark font-display">Ajouter un équipement</h2>
            <p class="text-sm text-gray-400 mt-0.5">Enregistrez un nouvel équipement dans le parc</p>
        </div>
        <a href="{{ route('equipment.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 border border-gray-200 hover:border-gray-300 px-3 py-2 rounded-xl transition-colors">
            <i data-lucide="arrow-left" style="width:14px;height:14px"></i>Retour
        </a>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-100 rounded-2xl px-4 py-3 mb-5 flex gap-3 fade-in">
        <i data-lucide="alert-circle" style="width:18px;height:18px;color:#dc2626;flex-shrink:0;margin-top:1px"></i>
        <ul class="text-sm text-red-700 space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('equipment.store') }}">
        @csrf

        {{-- Identité --}}
        <div class="section-card fade-in">
            <p class="section-title"><i data-lucide="info" style="width:14px;height:14px"></i>Identification</p>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="col-span-2">
                    <label class="form-label">Nom <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="form-input @error('name') !border-red-300 @enderror"
                           placeholder="Ex : Machine à coudre Juki DDL-8700">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Marque</label>
                    <input type="text" name="brand" value="{{ old('brand') }}"
                           class="form-input" placeholder="Juki, Brother, LG…">
                </div>
                <div>
                    <label class="form-label">Emplacement</label>
                    <div class="relative">
                        <i data-lucide="map-pin" style="width:14px;height:14px;position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#9ca3af;pointer-events:none"></i>
                        <input type="text" name="location" value="{{ old('location') }}"
                               class="form-input" style="padding-left:34px" placeholder="Atelier 1, Bureau…">
                    </div>
                </div>
            </div>
        </div>

        {{-- Type --}}
        <div class="section-card fade-in">
            <p class="section-title"><i data-lucide="layers" style="width:14px;height:14px"></i>Type d'équipement</p>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach(['machine_a_coudre'=>['Machine à coudre','scissors','text-purple-600','bg-purple-50'],'climatiseur'=>['Climatiseur','wind','text-blue-600','bg-blue-50'],'groupe_electrogene'=>['Groupe électrogène','zap','text-amber-600','bg-amber-50'],'ordinateur'=>['Ordinateur','monitor','text-gray-600','bg-gray-100'],'autre'=>['Autre','package','text-gray-500','bg-gray-50']] as $val => [$lbl,$icon,$tc,$bg])
                <label class="type-option" style="position:relative">
                    <input type="radio" name="type" value="{{ $val }}" @checked(old('type') === $val) required>
                    <span class="w-8 h-8 rounded-lg {{ $bg }} flex items-center justify-center shrink-0">
                        <i data-lucide="{{ $icon }}" style="width:16px;height:16px" class="{{ $tc }}"></i>
                    </span>
                    <span class="text-xs font-semibold text-dark">{{ $lbl }}</span>
                </label>
                @endforeach
            </div>
            @error('type')<p class="text-xs text-red-500 mt-2">{{ $message }}</p>@enderror
        </div>

        {{-- Maintenance --}}
        <div class="section-card fade-in">
            <p class="section-title"><i data-lucide="clock" style="width:14px;height:14px"></i>Maintenance préventive</p>
            <div class="mb-4">
                <label class="form-label">Intervalle de maintenance <span class="text-red-400">*</span></label>
                <div class="flex gap-2 items-center">
                    <input type="number" name="maintenance_interval_days" value="{{ old('maintenance_interval_days', 30) }}"
                           min="1" required
                           class="form-input w-32 @error('maintenance_interval_days') !border-red-300 @enderror">
                    <span class="text-sm text-gray-500">jours</span>
                </div>
                <p class="text-xs text-gray-400 mt-1.5">Fréquence recommandée d'entretien préventif</p>
                @error('maintenance_interval_days')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Notes <span class="text-gray-300 font-normal normal-case">(optionnel)</span></label>
                <textarea name="notes" rows="3" class="form-input" style="resize:none"
                          placeholder="Informations complémentaires, numéro de série, contrat de maintenance…">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-2 pb-6 fade-in">
            <a href="{{ route('equipment.index') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 border border-gray-200 px-4 py-2.5 rounded-xl transition-colors font-medium">
                <i data-lucide="x" style="width:14px;height:14px"></i>Annuler
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors shadow-sm shadow-primary-500/30">
                <i data-lucide="check" style="width:16px;height:16px"></i>Ajouter l'équipement
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>document.addEventListener('DOMContentLoaded',()=>lucide.createIcons());</script>
@endpush
