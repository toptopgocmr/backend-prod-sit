@extends('layouts.app')
@section('title', 'Nouveau client')

@section('breadcrumb')
    <a href="{{ route('clients.index') }}" class="hover:text-gray-600 transition-colors">Clients</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Nouveau client</span>
@endsection

@section('content')
<div class="max-w-2xl space-y-5" x-data="{
    mode: '{{ old('mode','nouveau') }}',
    gender: '{{ old('gender','femme') }}'
}">

    {{-- En-tête + bascule --}}
    <div class="flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-primary/10 flex items-center justify-center">
            <i data-lucide="user-plus" class="w-5 h-5 text-primary"></i>
        </div>
        <div class="flex-1">
            <h2 class="text-lg font-display font-bold text-dark">Enregistrement client</h2>
            <p class="text-xs text-gray-400">Renseignez les informations de contact</p>
        </div>
        {{-- Toggle Nouveau / Ancien --}}
        <div class="flex rounded-xl border border-gray-200 overflow-hidden text-sm font-semibold">
            <button type="button"
                    @click="mode='nouveau'"
                    :class="mode==='nouveau' ? 'bg-dark text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
                    class="px-4 py-2 transition-all flex items-center gap-1.5">
                <i data-lucide="user-plus" style="width:13px;height:13px"></i>
                Nouveau client
            </button>
            <button type="button"
                    @click="mode='ancien'"
                    :class="mode==='ancien' ? 'bg-dark text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
                    class="px-4 py-2 transition-all flex items-center gap-1.5 border-l border-gray-200">
                <i data-lucide="users" style="width:13px;height:13px"></i>
                Ancien client
            </button>
        </div>
    </div>

    {{-- Bannière mode --}}
    <div x-show="mode==='ancien'" class="flex items-center gap-3 bg-dark/5 border border-dark/10 rounded-xl px-4 py-3">
        <i data-lucide="info" class="w-4 h-4 text-dark/60 shrink-0"></i>
        <p class="text-xs text-dark/70">
            Mode <strong>Ancien client</strong> — le formulaire inclut les mesures corporelles pour un suivi immédiat en atelier.
        </p>
    </div>

    {{-- Erreurs --}}
    @if($errors->any())
    <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3.5 rounded-xl text-sm">
        <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 shrink-0 mt-0.5"></i>
        <ul class="space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('clients.store') }}">
        @csrf
        <input type="hidden" name="mode" :value="mode">

        {{-- ── Identité ────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4 mb-5">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4 text-primary"></i>
                Identité
            </h3>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-2">Genre <span class="text-red-400">*</span></label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['homme' => 'Homme', 'femme' => 'Femme', 'non_precise' => 'Non précisé'] as $val => $lbl)
                        <label class="flex items-center justify-center py-2.5 rounded-xl border cursor-pointer text-sm font-semibold transition-all"
                               :class="gender === '{{ $val }}' ? 'border-primary bg-orange-50 text-primary' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                            <input type="radio" name="gender" value="{{ $val }}"
                                   x-model="gender" class="sr-only">
                            {{ $lbl }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prénom <span class="text-red-400">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}"
                           placeholder="Ex : Isabelle" required autofocus
                           class="w-full px-3 py-2.5 rounded-xl border @error('first_name') border-red-300 bg-red-50 @else border-gray-200 @enderror text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    @error('first_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom <span class="text-red-400">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}"
                           placeholder="Ex : Moukala" required
                           class="w-full px-3 py-2.5 rounded-xl border @error('last_name') border-red-300 bg-red-50 @else border-gray-200 @enderror text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    @error('last_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Date de naissance</label>
                <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                       max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                       class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
            </div>
        </div>

        {{-- ── Contact ──────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4 mb-5">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                <i data-lucide="phone" class="w-4 h-4 text-primary"></i>
                Contact
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Téléphone <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i data-lucide="phone" style="width:15px;height:15px" class="text-gray-400"></i>
                        </span>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               placeholder="+242 06 000 0001" required
                               class="w-full pl-9 pr-3 py-2.5 rounded-xl border @error('phone') border-red-300 bg-red-50 @else border-gray-200 @enderror text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    @error('phone')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">E-mail</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i data-lucide="mail" style="width:15px;height:15px" class="text-gray-400"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="exemple@email.com"
                               class="w-full pl-9 pr-3 py-2.5 rounded-xl border @error('email') border-red-300 bg-red-50 @else border-gray-200 @enderror text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Ville</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i data-lucide="map-pin" style="width:15px;height:15px" class="text-gray-400"></i>
                        </span>
                        <input type="text" name="city" value="{{ old('city') }}"
                               placeholder="Ex : Brazzaville, Pointe-Noire..."
                               class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Quartier / Adresse</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           placeholder="Ex : Plateau, Moungali..."
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
            </div>
        </div>

        {{-- ── Mesures (Ancien client seulement) ──────────── --}}
        <div x-show="mode==='ancien'" x-transition class="bg-white rounded-2xl border border-dark/10 p-5 mb-5">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2 mb-1">
                <i data-lucide="ruler" class="w-4 h-4 text-dark/60"></i>
                Mesures corporelles
            </h3>
            <p class="text-xs text-gray-400 mb-4">Toutes les mesures sont en centimètres (cm)</p>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Libellé des mesures</label>
                <input type="text" name="mesure_label" value="{{ old('mesure_label','Mesures initiales') }}"
                       placeholder="Ex : Mesures initiales, Tenue de mariage..."
                       class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-dark/10 focus:border-dark transition-all mb-4">
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @php
                $mesures = [
                    'poitrine'          => ['label' => 'Poitrine', 'icon' => '◉', 'placeholder' => '90'],
                    'taille'            => ['label' => 'Taille',   'icon' => '◉', 'placeholder' => '70'],
                    'hanches'           => ['label' => 'Hanches',  'icon' => '◉', 'placeholder' => '95'],
                    'epaules'           => ['label' => 'Épaules',  'icon' => '◉', 'placeholder' => '38'],
                    'cou'               => ['label' => 'Cou',      'icon' => '◉', 'placeholder' => '37'],
                    'bras'              => ['label' => 'Bras',     'icon' => '|', 'placeholder' => '60'],
                    'longueur_manche'   => ['label' => 'Manche',   'icon' => '|', 'placeholder' => '60'],
                    'longueur_robe'     => ['label' => 'Robe/Boubou','icon'=>'|', 'placeholder' => '110'],
                    'longueur_pantalon' => ['label' => 'Pantalon', 'icon' => '|', 'placeholder' => '100'],
                    'entrejambe'        => ['label' => 'Entrejambe','icon'=> '|', 'placeholder' => '75'],
                ];
                @endphp
                @foreach($mesures as $field => $meta)
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ $meta['label'] }} <span class="text-gray-300 font-normal">cm</span></label>
                    <input type="number" name="mesures[{{ $field }}]" value="{{ old('mesures.'.$field) }}"
                           placeholder="{{ $meta['placeholder'] }}" step="0.5" min="1"
                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-dark/10 focus:border-dark transition-all">
                </div>
                @endforeach
            </div>

            <div class="mt-3">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Notes atelier</label>
                <textarea name="mesures[notes]" rows="2"
                          placeholder="Particularités morphologiques, préférences de coupe..."
                          class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark resize-none focus:outline-none focus:ring-2 focus:ring-dark/10 focus:border-dark transition-all">{{ old('mesures.notes') }}</textarea>
            </div>
        </div>

        {{-- ── Notes internes ──────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-5">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2 mb-3">
                <i data-lucide="file-text" class="w-4 h-4 text-primary"></i>
                Notes internes
            </h3>
            <textarea name="notes" rows="3"
                      placeholder="Préférences, allergies tissu, informations utiles pour l'atelier..."
                      class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark resize-none focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">{{ old('notes') }}</textarea>
        </div>

        {{-- ── Boutons ──────────────────────────────────── --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('clients.index') }}"
               class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                Annuler
            </a>
            <button type="submit"
                    class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-bold hover:bg-orange-600 active:scale-95 transition-all shadow-sm shadow-primary/20">
                <i data-lucide="user-plus" style="width:15px;height:15px"></i>
                <span x-text="mode==='ancien' ? 'Enregistrer le client + mesures' : 'Créer le client'"></span>
            </button>
        </div>

    </form>
</div>
@endsection
