@extends('layouts.app')
@section('title', 'Paramètres')

@section('breadcrumb')
    <span>Admin</span>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Paramètres</span>
@endsection

@section('content')
<div class="space-y-6 max-w-3xl">

    {{-- ── En-tête ──────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center">
            <i data-lucide="settings" class="w-6 h-6 text-primary"></i>
        </div>
        <div>
            <h2 class="text-xl font-display font-bold text-dark">Paramètres de l'application</h2>
            <p class="text-sm text-gray-400">Configurez les paramètres généraux du système</p>
        </div>
    </div>

    {{-- ── Erreurs de validation ────────────────────────────────────── --}}
    @if($errors->any())
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3.5 rounded-xl text-sm">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 shrink-0 mt-0.5"></i>
            <ul class="space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" x-data="{ changed: false }" @change="changed = true">
        @csrf
        @method('PUT')

        {{-- ── Informations générales ───────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-5">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center">
                    <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                </div>
                <h3 class="font-display font-semibold text-dark">Informations générales</h3>
            </div>
            <div class="p-6 space-y-5">

                {{-- Nom de l'application --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Nom de l'application <span class="text-red-400">*</span>
                    </label>
                    <input
                        type="text"
                        name="app_name"
                        value="{{ old('app_name', $settings['app_name']) }}"
                        class="w-full px-4 py-2.5 rounded-xl border @error('app_name') border-red-300 bg-red-50 @else border-gray-200 @enderror text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                        placeholder="Ex : GSIT"
                    >
                    @error('app_name')
                        <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                            <i data-lucide="alert-circle" style="width:12px;height:12px"></i> {{ $message }}
                        </p>
                    @else
                        <p class="text-xs text-gray-400 mt-1">Affiché dans le titre de chaque page et les e-mails.</p>
                    @enderror
                </div>

                {{-- Devise --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Devise <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 pointer-events-none">
                            <i data-lucide="circle-dollar-sign" style="width:16px;height:16px"></i>
                        </span>
                        <input
                            type="text"
                            name="currency"
                            value="{{ old('currency', $settings['currency']) }}"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border @error('currency') border-red-300 bg-red-50 @else border-gray-200 @enderror text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            placeholder="Ex : FCFA, EUR, USD"
                        >
                    </div>
                    @error('currency')
                        <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                            <i data-lucide="alert-circle" style="width:12px;height:12px"></i> {{ $message }}
                        </p>
                    @else
                        <p class="text-xs text-gray-400 mt-1">Symbole ou code affiché sur les montants dans l'interface.</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- ── Stock ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-5">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-orange-50 flex items-center justify-center">
                    <i data-lucide="package" class="w-4 h-4 text-primary"></i>
                </div>
                <h3 class="font-display font-semibold text-dark">Stock</h3>
            </div>
            <div class="p-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Seuil d'alerte stock bas <span class="text-red-400">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 pointer-events-none">
                                <i data-lucide="alert-triangle" style="width:16px;height:16px"></i>
                            </span>
                            <input
                                type="number"
                                name="low_stock_threshold"
                                value="{{ old('low_stock_threshold', $settings['low_stock_threshold']) }}"
                                min="1" max="9999"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border @error('low_stock_threshold') border-red-300 bg-red-50 @else border-gray-200 @enderror text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            >
                        </div>
                        <span class="text-sm text-gray-500 font-medium whitespace-nowrap">unités</span>
                    </div>
                    @error('low_stock_threshold')
                        <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                            <i data-lucide="alert-circle" style="width:12px;height:12px"></i> {{ $message }}
                        </p>
                    @else
                        <p class="text-xs text-gray-400 mt-1">
                            En dessous de ce seuil, une alerte sera déclenchée pour le produit concerné.
                        </p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ── Notifications par e-mail ─────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-5">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center">
                    <i data-lucide="mail" class="w-4 h-4 text-purple-600"></i>
                </div>
                <h3 class="font-display font-semibold text-dark">Notifications e-mail</h3>
            </div>
            <div class="p-6 space-y-5">

                {{-- Adresse expéditeur --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Adresse e-mail expéditeur
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 pointer-events-none">
                            <i data-lucide="at-sign" style="width:16px;height:16px"></i>
                        </span>
                        <input
                            type="email"
                            name="mail_from_address"
                            value="{{ old('mail_from_address', $settings['mail_from_address']) }}"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border @error('mail_from_address') border-red-300 bg-red-50 @else border-gray-200 @enderror text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            placeholder="noreply@monentreprise.com"
                        >
                    </div>
                    @error('mail_from_address')
                        <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                            <i data-lucide="alert-circle" style="width:12px;height:12px"></i> {{ $message }}
                        </p>
                    @else
                        <p class="text-xs text-gray-400 mt-1">Adresse utilisée comme expéditeur dans les e-mails automatiques.</p>
                    @enderror
                </div>

                {{-- Nom expéditeur --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Nom d'affichage expéditeur
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 pointer-events-none">
                            <i data-lucide="user" style="width:16px;height:16px"></i>
                        </span>
                        <input
                            type="text"
                            name="mail_from_name"
                            value="{{ old('mail_from_name', $settings['mail_from_name']) }}"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm text-dark focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            placeholder="Ex : GSIT Notifications"
                        >
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Nom affiché dans la boîte de réception des destinataires.</p>
                </div>

            </div>
        </div>

        {{-- ── Informations système (lecture seule) ─────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-gray-50 flex items-center justify-center">
                    <i data-lucide="cpu" class="w-4 h-4 text-gray-500"></i>
                </div>
                <h3 class="font-display font-semibold text-dark">Informations système</h3>
                <span class="ml-auto text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full font-medium">Lecture seule</span>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-surface rounded-xl p-4 border border-gray-100">
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1.5">Laravel</p>
                        <p class="text-sm font-bold text-dark">{{ app()->version() }}</p>
                    </div>
                    <div class="bg-surface rounded-xl p-4 border border-gray-100">
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1.5">PHP</p>
                        <p class="text-sm font-bold text-dark">{{ PHP_VERSION }}</p>
                    </div>
                    <div class="bg-surface rounded-xl p-4 border border-gray-100">
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1.5">Environnement</p>
                        <p class="text-sm font-bold text-dark capitalize">{{ app()->environment() }}</p>
                    </div>
                    <div class="bg-surface rounded-xl p-4 border border-gray-100">
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1.5">Debug</p>
                        <span class="inline-flex items-center gap-1.5 text-sm font-bold {{ config('app.debug') ? 'text-orange-500' : 'text-emerald-600' }}">
                            <span class="w-2 h-2 rounded-full {{ config('app.debug') ? 'bg-orange-400 pulse-dot' : 'bg-emerald-400' }}"></span>
                            {{ config('app.debug') ? 'ON' : 'OFF' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Boutons ─────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between">
            <p x-show="changed" x-transition class="text-xs text-amber-600 flex items-center gap-1.5 font-medium">
                <i data-lucide="clock" style="width:14px;height:14px"></i>
                Modifications non enregistrées
            </p>
            <div class="flex items-center gap-3 ml-auto">
                <a href="{{ route('dashboard') }}"
                   class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                    Annuler
                </a>
                <button type="submit"
                        class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary-600 active:scale-95 transition-all shadow-sm shadow-primary/20">
                    <i data-lucide="save" style="width:16px;height:16px"></i>
                    Enregistrer
                </button>
            </div>
        </div>

    </form>

</div>
@endsection
