@extends('layouts.app')
@section('title', 'Nouveau client')

@section('breadcrumb')
    <a href="{{ route('clients.index') }}" class="hover:text-gray-600 transition-colors">Clients</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Nouveau client</span>
@endsection

@section('content')
<div class="max-w-2xl space-y-5" x-data="{
    mode: '{{ old('mode', request('mode','nouveau')) }}',
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
        <div x-show="mode==='ancien'" x-transition class="space-y-4 mb-5">

            {{-- Libellé --}}
            <div class="bg-white rounded-2xl border border-dark/10 p-5">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2 mb-3">
                    <i data-lucide="ruler" class="w-4 h-4 text-dark/60"></i>
                    Mesures corporelles
                    <span class="ml-auto text-xs font-normal text-gray-400">en centimètres (cm)</span>
                </h3>
                <input type="text" name="mesure_label" value="{{ old('mesure_label','Mesures initiales') }}"
                       placeholder="Ex : Mesures initiales, Tenue de mariage, Uniforme..."
                       class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-dark/10 focus:border-dark transition-all">
            </div>

            @php
            $inputClass = 'w-full px-3 py-2 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-dark/10 focus:border-dark transition-all';
            $labelClass = 'block text-xs font-semibold text-gray-500 mb-1';
            @endphp

            {{-- ═══ FEMME ══════════════════════════════════════════ --}}
            <div x-show="gender === 'femme'" class="space-y-4">

                {{-- Haut du corps --}}
                <div class="bg-pink-50/60 rounded-2xl border border-pink-100 p-5">
                    <p class="text-xs font-bold text-pink-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i data-lucide="shirt" style="width:13px;height:13px"></i> Haut du corps
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach([
                            'f_longueur_epaule'      => ['Longueur épaule',    '38'],
                            'f_tour_poitrine'        => ['Tour de poitrine',   '92'],
                            'f_tour_taille'          => ['Tour de taille',     '70'],
                            'f_petites_hanches'      => ['Petites hanches',    '88'],
                            'f_grandes_hanches'      => ['Grandes hanches',    '96'],
                            'f_hauteur_saillant'     => ['Hauteur saillant',   '26'],
                            'f_ecart_saillants'      => ['Écart saillants',    '18'],
                            'f_hauteur_buste_devant' => ['Buste devant',       '32'],
                            'f_hauteur_buste_dos'    => ['Buste dos',          '38'],
                            'f_longueur_cote_buste'  => ['Côté buste',         '16'],
                            'f_tour_manche'          => ['Tour de manche',     '28'],
                            'f_longueur_manche'      => ['Longueur manche',    '60'],
                            'f_carrure_devant'       => ['Carrure devant',     '34'],
                            'f_carrure_dos'          => ['Carrure dos',        '36'],
                            'f_longueur_veste'       => ['Longueur veste',     '68'],
                            'f_tour_encolure'        => ['Tour encolure',      '38'],
                            'f_hauteur_fessier'      => ['Hauteur fessier',    '22'],
                        ] as $field => [$label, $ph])
                        <div>
                            <label class="{{ $labelClass }}">{{ $label }} <span class="text-gray-300 font-normal">cm</span></label>
                            <input type="number" name="mesures[{{ $field }}]" value="{{ old('mesures.'.$field) }}"
                                   placeholder="{{ $ph }}" step="0.5" min="1" class="{{ $inputClass }}">
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Bas du corps --}}
                <div class="bg-pink-50/60 rounded-2xl border border-pink-100 p-5">
                    <p class="text-xs font-bold text-pink-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i data-lucide="scissors" style="width:13px;height:13px"></i> Bas du corps
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach([
                            'f_tour_ceinture'     => ['Tour de ceinture',     '72'],
                            'f_tour_bassin'       => ['Tour de bassin',       '96'],
                            'f_hauteur_bassin'    => ['Hauteur bassin',       '20'],
                            'f_longueur_assise'   => ["Longueur d'assise",    '30'],
                            'f_fourche_devant'    => ['Fourche devant',       '28'],
                            'f_fourche_dos'       => ['Fourche dos',          '32'],
                            'f_entrejambe'        => ['Entrejambe',           '75'],
                            'f_longueur_pantalon' => ['Longueur pantalon',    '100'],
                            'f_tour_cuisse'       => ['Tour de cuisse',       '56'],
                            'f_largeur_bas'       => ['Largeur du bas',       '16'],
                        ] as $field => [$label, $ph])
                        <div>
                            <label class="{{ $labelClass }}">{{ $label }} <span class="text-gray-300 font-normal">cm</span></label>
                            <input type="number" name="mesures[{{ $field }}]" value="{{ old('mesures.'.$field) }}"
                                   placeholder="{{ $ph }}" step="0.5" min="1" class="{{ $inputClass }}">
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Longueurs robes & jupes --}}
                <div class="bg-pink-50/60 rounded-2xl border border-pink-100 p-5">
                    <p class="text-xs font-bold text-pink-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i data-lucide="layers" style="width:13px;height:13px"></i> Longueurs robes & jupes
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach([
                            'f_robe_longue'       => ['Robe longue',        '120'],
                            'f_robe_avant_genoux' => ['Robe avant genoux',  '58'],
                            'f_robe_apres_genoux' => ['Robe après genoux',  '65'],
                            'f_robe_trois_quarts' => ['Robe ¾',             '90'],
                            'f_jupe_longue'       => ['Jupe longue',        '100'],
                            'f_jupe_genoux'       => ['Jupe genoux',        '52'],
                            'f_jupe_trois_quarts' => ['Jupe ¾',             '75'],
                        ] as $field => [$label, $ph])
                        <div>
                            <label class="{{ $labelClass }}">{{ $label }} <span class="text-gray-300 font-normal">cm</span></label>
                            <input type="number" name="mesures[{{ $field }}]" value="{{ old('mesures.'.$field) }}"
                                   placeholder="{{ $ph }}" step="0.5" min="1" class="{{ $inputClass }}">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ═══ HOMME ══════════════════════════════════════════ --}}
            <div x-show="gender === 'homme'" class="space-y-4">

                {{-- Haut du corps --}}
                <div class="bg-blue-50/60 rounded-2xl border border-blue-100 p-5">
                    <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i data-lucide="shirt" style="width:13px;height:13px"></i> Haut du corps
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach([
                            'h_epaule'             => ['Épaule',            '44'],
                            'h_manche_longue'      => ['Manche longue',     '64'],
                            'h_manche_courte'      => ['Manche courte',     '22'],
                            'h_tour_poitrine'      => ['Tour de poitrine',  '96'],
                            'h_tour_taille_ventre' => ['Taille / ventre',   '88'],
                            'h_carrure_dos'        => ['Carrure dos',       '42'],
                            'h_longueur_chemise'   => ['Longueur chemise',  '72'],
                            'h_longueur_veste'     => ['Longueur veste',    '75'],
                            'h_contour_manche'     => ['Contour manche',    '30'],
                            'h_tour_col'           => ['Tour de col',       '40'],
                            'h_longueur_devant'    => ['Longueur devant',   '68'],
                        ] as $field => [$label, $ph])
                        <div>
                            <label class="{{ $labelClass }}">{{ $label }} <span class="text-gray-300 font-normal">cm</span></label>
                            <input type="number" name="mesures[{{ $field }}]" value="{{ old('mesures.'.$field) }}"
                                   placeholder="{{ $ph }}" step="0.5" min="1" class="{{ $inputClass }}">
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Bas du corps --}}
                <div class="bg-blue-50/60 rounded-2xl border border-blue-100 p-5">
                    <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i data-lucide="scissors" style="width:13px;height:13px"></i> Bas du corps
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach([
                            'h_tour_ceinture'    => ['Tour de ceinture',   '86'],
                            'h_tour_bassin'      => ['Tour de bassin',     '96'],
                            'h_tour_cuisse'      => ['Tour de cuisse',     '58'],
                            'h_largeur_genoux'   => ['Largeur genoux',     '22'],
                            'h_tour_mollet'      => ['Tour de mollet',     '38'],
                            'h_diametre_bas'     => ['Diamètre du bas',    '18'],
                            'h_longueur_pantalon'=> ['Longueur pantalon',  '104'],
                            'h_longueur_culotte' => ['Longueur culotte',   '62'],
                            'h_pisset'           => ['Pisset (braguette)', '22'],
                        ] as $field => [$label, $ph])
                        <div>
                            <label class="{{ $labelClass }}">{{ $label }} <span class="text-gray-300 font-normal">cm</span></label>
                            <input type="number" name="mesures[{{ $field }}]" value="{{ old('mesures.'.$field) }}"
                                   placeholder="{{ $ph }}" step="0.5" min="1" class="{{ $inputClass }}">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ═══ NON PRÉCISÉ — champs basiques ════════════════ --}}
            <div x-show="gender === 'non_precise'">
                <div class="bg-gray-50 rounded-2xl border border-gray-100 p-5">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Mesures générales</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach([
                            'poitrine'           => ['Poitrine',   '90'],
                            'taille'             => ['Taille',     '70'],
                            'hanches'            => ['Hanches',    '96'],
                            'epaules'            => ['Épaules',    '40'],
                            'cou'                => ['Cou',        '38'],
                            'bras'               => ['Bras',       '60'],
                            'longueur_manche'    => ['Manche',     '60'],
                            'longueur_robe'      => ['Robe/Boubou','110'],
                            'longueur_pantalon'  => ['Pantalon',   '100'],
                            'entrejambe'         => ['Entrejambe', '75'],
                        ] as $field => [$label, $ph])
                        <div>
                            <label class="{{ $labelClass }}">{{ $label }} <span class="text-gray-300 font-normal">cm</span></label>
                            <input type="number" name="mesures[{{ $field }}]" value="{{ old('mesures.'.$field) }}"
                                   placeholder="{{ $ph }}" step="0.5" min="1" class="{{ $inputClass }}">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Notes atelier (toujours visible si mode=ancien) --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <label class="{{ $labelClass }} mb-2">Notes atelier</label>
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
