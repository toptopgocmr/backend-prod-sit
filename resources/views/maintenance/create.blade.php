@extends('layouts.app')

@section('title', 'Nouvelle intervention')

@section('breadcrumb')
    <a href="{{ route('maintenance.index') }}" class="hover:text-primary-500 transition-colors">Interventions</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Nouvelle intervention</span>
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
    .type-option{border:1.5px solid #e5e7eb;border-radius:12px;padding:12px 16px;cursor:pointer;transition:border-color .15s,background .15s;background:#f9fafb;display:flex;align-items:center;gap:10px;position:relative;flex:1}
    .type-option input{position:absolute;opacity:0}
    .type-option:has(input[value="preventive"]:checked){border-color:#2563eb;background:#eff6ff}
    .type-option:has(input[value="corrective"]:checked){border-color:#d97706;background:#fffbeb}
    .type-option:has(input[value="urgence"]:checked){border-color:#dc2626;background:#fef2f2}
    @keyframes fadeSlide{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
    .fade-in{animation:fadeSlide .22s ease both}
    .fade-in:nth-child(2){animation-delay:.06s}
    .fade-in:nth-child(3){animation-delay:.12s}
    .fade-in:nth-child(4){animation-delay:.18s}
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="flex items-center justify-between mb-6 fade-in">
        <div>
            <h2 class="text-xl font-bold text-dark font-display">Nouvelle intervention</h2>
            <p class="text-sm text-gray-400 mt-0.5">Signaler une intervention de maintenance</p>
        </div>
        <a href="{{ route('maintenance.index') }}"
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

    <form method="POST" action="{{ route('maintenance.store') }}">
        @csrf

        {{-- Équipement --}}
        <div class="section-card fade-in">
            <p class="section-title"><i data-lucide="tool" style="width:14px;height:14px"></i>Équipement concerné</p>
            <div>
                <label class="form-label">Équipement <span class="text-red-400">*</span></label>
                <div class="relative">
                    <i data-lucide="search" style="width:15px;height:15px;position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9ca3af;pointer-events:none"></i>
                    <select name="equipment_id" required
                            class="form-input @error('equipment_id') !border-red-300 !bg-red-50 @enderror"
                            style="padding-left:36px">
                        <option value="">Sélectionner un équipement…</option>
                        @foreach($equipment as $eq)
                            <option value="{{ $eq->id }}"
                                @selected(old('equipment_id', request('equipment_id')) == $eq->id)>
                                {{ $eq->name }}{{ $eq->location ? ' — '.$eq->location : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('equipment_id')<p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Type --}}
        <div class="section-card fade-in">
            <p class="section-title"><i data-lucide="layers" style="width:14px;height:14px"></i>Type d'intervention</p>
            <div class="flex gap-3">
                <label class="type-option">
                    <input type="radio" name="type" value="preventive" @checked(old('type','preventive')==='preventive') required>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                        <i data-lucide="shield-check" style="width:16px;height:16px;color:#2563eb"></i>
                    </span>
                    <div>
                        <p class="text-xs font-bold text-dark">Préventive</p>
                        <p class="text-xs text-gray-400">Entretien planifié</p>
                    </div>
                </label>
                <label class="type-option">
                    <input type="radio" name="type" value="corrective" @checked(old('type')==='corrective')>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                        <i data-lucide="wrench" style="width:16px;height:16px;color:#d97706"></i>
                    </span>
                    <div>
                        <p class="text-xs font-bold text-dark">Corrective</p>
                        <p class="text-xs text-gray-400">Suite à une panne</p>
                    </div>
                </label>
                <label class="type-option">
                    <input type="radio" name="type" value="urgence" @checked(old('type')==='urgence')>
                    <span class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                        <i data-lucide="zap" style="width:16px;height:16px;color:#dc2626"></i>
                    </span>
                    <div>
                        <p class="text-xs font-bold text-dark">Urgence</p>
                        <p class="text-xs text-gray-400">Priorité critique</p>
                    </div>
                </label>
            </div>
            @error('type')<p class="text-xs text-red-500 mt-2">{{ $message }}</p>@enderror
        </div>

        {{-- Détails --}}
        <div class="section-card fade-in">
            <p class="section-title"><i data-lucide="file-text" style="width:14px;height:14px"></i>Détails</p>

            {{-- Titre --}}
            <div class="mb-4">
                <label class="form-label">Titre <span class="text-red-400">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="200"
                       class="form-input @error('title') !border-red-300 @enderror"
                       placeholder="Ex : Remplacement courroie machine n°3">
                @error('title')<p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>@enderror
            </div>

            {{-- Description --}}
            <div class="mb-4">
                <label class="form-label">Description <span class="text-red-400">*</span></label>
                <textarea name="description" rows="4" required
                          class="form-input @error('description') !border-red-300 @enderror"
                          style="resize:none"
                          placeholder="Décrivez les symptômes, actions à réaliser, observations…">{{ old('description') }}</textarea>
                @error('description')<p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>@enderror
            </div>

            {{-- Date planifiée --}}
            <div>
                <label class="form-label">Date planifiée <span class="text-gray-300 font-normal normal-case">(optionnel)</span></label>
                <div class="relative">
                    <i data-lucide="calendar" style="width:14px;height:14px;position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#9ca3af;pointer-events:none"></i>
                    <input type="date" name="scheduled_date"
                           value="{{ old('scheduled_date') }}"
                           class="form-input @error('scheduled_date') !border-red-300 @enderror"
                           style="padding-left:34px">
                </div>
                @error('scheduled_date')<p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Avertissement urgence --}}
        <div id="urgence-warning" class="hidden bg-red-50 border border-red-100 rounded-2xl px-4 py-3 mb-4 flex items-center gap-3">
            <i data-lucide="alert-triangle" style="width:18px;height:18px;color:#dc2626;flex-shrink:0"></i>
            <p class="text-sm text-red-700 font-medium">
                Une intervention de type <strong>urgence</strong> ou <strong>corrective</strong> passera automatiquement l'équipement en statut « En panne ».
            </p>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-2 pb-6 fade-in">
            <a href="{{ route('maintenance.index') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 border border-gray-200 px-4 py-2.5 rounded-xl transition-colors font-medium">
                <i data-lucide="x" style="width:14px;height:14px"></i>Annuler
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors shadow-sm shadow-primary-500/30">
                <i data-lucide="check" style="width:16px;height:16px"></i>
                Signaler l'intervention
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
    const radios  = document.querySelectorAll('input[name="type"]');
    const warning = document.getElementById('urgence-warning');
    function checkType() {
        const val = document.querySelector('input[name="type"]:checked')?.value;
        warning.classList.toggle('hidden', !['corrective','urgence'].includes(val));
        warning.classList.toggle('flex',   ['corrective','urgence'].includes(val));
    }
    radios.forEach(r => r.addEventListener('change', checkType));
    checkType();
});
</script>
@endpush
