@extends('layouts.app')
@section('title', 'Nouveau client')

@section('breadcrumb')
    <a href="{{ route('clients.index') }}" class="hover:text-gray-600 transition-colors">Clients</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Nouveau client</span>
@endsection

@section('content')
<div class="max-w-2xl space-y-5">

    {{-- En-tête --}}
    <div class="flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-primary/10 flex items-center justify-center">
            <i data-lucide="user-plus" class="w-5 h-5 text-primary"></i>
        </div>
        <div>
            <h2 class="text-lg font-display font-bold text-dark">Nouveau client</h2>
            <p class="text-xs text-gray-400">Renseignez les informations de contact du client</p>
        </div>
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

    <form method="POST" action="{{ route('clients.store') }}" x-data="{ gender: '{{ old('gender', 'femme') }}' }">
        @csrf

        {{-- ── Identité ──────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4 mb-5">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4 text-primary"></i>
                Identité
            </h3>

            {{-- Genre --}}
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

            {{-- Prénom + Nom --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Prénom <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}"
                           placeholder="Ex : Isabelle" required autofocus
                           class="w-full px-3 py-2.5 rounded-xl border @error('first_name') border-red-300 bg-red-50 @else border-gray-200 @enderror
                                  text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    @error('first_name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Nom <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}"
                           placeholder="Ex : Moukala" required
                           class="w-full px-3 py-2.5 rounded-xl border @error('last_name') border-red-300 bg-red-50 @else border-gray-200 @enderror
                                  text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    @error('last_name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Date de naissance --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Date de naissance</label>
                <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                       max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                       class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark
                              focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
            </div>
        </div>

        {{-- ── Contact ────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4 mb-5">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                <i data-lucide="phone" class="w-4 h-4 text-primary"></i>
                Contact
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Téléphone <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i data-lucide="phone" style="width:15px;height:15px" class="text-gray-400"></i>
                        </span>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               placeholder="+242 06 000 0001" required
                               class="w-full pl-9 pr-3 py-2.5 rounded-xl border @error('phone') border-red-300 bg-red-50 @else border-gray-200 @enderror
                                      text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    @error('phone')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">E-mail</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i data-lucide="mail" style="width:15px;height:15px" class="text-gray-400"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="exemple@email.com"
                               class="w-full pl-9 pr-3 py-2.5 rounded-xl border @error('email') border-red-300 bg-red-50 @else border-gray-200 @enderror
                                      text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Ville + Adresse --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Ville</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i data-lucide="map-pin" style="width:15px;height:15px" class="text-gray-400"></i>
                        </span>
                        <input type="text" name="city" value="{{ old('city') }}"
                               placeholder="Ex : Brazzaville, Pointe-Noire..."
                               class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark
                                      focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Quartier / Adresse</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           placeholder="Ex : Plateau, Moungali..."
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark
                                  focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
            </div>
        </div>

        {{-- ── Notes ─────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-5">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2 mb-3">
                <i data-lucide="file-text" class="w-4 h-4 text-primary"></i>
                Notes internes
            </h3>
            <textarea name="notes" rows="3"
                      placeholder="Préférences, allergies tissu, informations utiles pour l'atelier..."
                      class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-dark resize-none
                             focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">{{ old('notes') }}</textarea>
        </div>

        {{-- ── Boutons ────────────────────────────────────── --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('clients.index') }}"
               class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                Annuler
            </a>
            <button type="submit"
                    class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-bold
                           hover:bg-orange-600 active:scale-95 transition-all shadow-sm shadow-primary/20">
                <i data-lucide="user-plus" style="width:15px;height:15px"></i>
                Créer le client
            </button>
        </div>

    </form>
</div>
@endsection
