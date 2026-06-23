@extends('layouts.app')
@section('title', 'Nouvel utilisateur')

@section('breadcrumb')
    <a href="{{ route('users.index') }}" class="hover:text-gray-600 transition-colors">Utilisateurs</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Nouvel utilisateur</span>
@endsection

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <i data-lucide="user-plus" class="w-5 h-5 text-primary"></i>
            </div>
            <div>
                <h2 class="font-display font-bold text-dark">Nouvel utilisateur</h2>
                <p class="text-xs text-gray-400">Créer un compte d'accès à la plateforme</p>
            </div>
        </div>
        <a href="{{ route('users.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
            <i data-lucide="arrow-left" style="width:15px;height:15px"></i>
            Retour
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
        @csrf

        <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            <h3 class="font-display font-bold text-dark text-sm flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-gray-400"></i>
                Informations du compte
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Nom complet --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-dark mb-1.5">
                        Nom complet <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="Ex: Jean Dupont"
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all @error('name') border-red-400 @enderror">
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-semibold text-dark mb-1.5">
                        Adresse email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="jean@gsit.art"
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all @error('email') border-red-400 @enderror">
                </div>

                {{-- Téléphone --}}
                <div>
                    <label class="block text-sm font-semibold text-dark mb-1.5">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           placeholder="+242 06 000 0000"
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all">
                </div>

                {{-- Rôle --}}
                <div>
                    <label class="block text-sm font-semibold text-dark mb-1.5">
                        Rôle <span class="text-red-500">*</span>
                    </label>
                    <select name="role" required
                            class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all bg-white @error('role') border-red-400 @enderror">
                        <option value="">— Choisir un rôle —</option>
                        <option value="admin"         {{ old('role')=='admin'         ? 'selected' : '' }}>Administrateur</option>
                        <option value="couturier"     {{ old('role')=='couturier'     ? 'selected' : '' }}>Couturier(ère)</option>
                        <option value="stock_manager" {{ old('role')=='stock_manager' ? 'selected' : '' }}>Responsable Stock</option>
                        <option value="cashier"       {{ old('role')=='cashier'       ? 'selected' : '' }}>Caissier(ère)</option>
                        <option value="delivery"      {{ old('role')=='delivery'      ? 'selected' : '' }}>Livreur</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Le rôle détermine les accès à la plateforme.</p>
                </div>

                {{-- Mot de passe --}}
                <div>
                    <label class="block text-sm font-semibold text-dark mb-1.5">
                        Mot de passe <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" required minlength="8"
                           placeholder="Minimum 8 caractères"
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all @error('password') border-red-400 @enderror">
                </div>

                {{-- Confirmation --}}
                <div>
                    <label class="block text-sm font-semibold text-dark mb-1.5">
                        Confirmer le mot de passe <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" required
                           placeholder="Répéter le mot de passe"
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all">
                </div>

            </div>
        </div>

        {{-- Tableau des permissions par rôle --}}
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5">
            <p class="text-sm font-semibold text-blue-800 mb-3 flex items-center gap-2">
                <i data-lucide="shield" class="w-4 h-4"></i>
                Accès par rôle
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-blue-700">
                <div class="bg-white rounded-xl p-3 border border-blue-100">
                    <p class="font-bold mb-1">Administrateur</p>
                    <p class="text-gray-500">Accès complet à toute la plateforme</p>
                </div>
                <div class="bg-white rounded-xl p-3 border border-blue-100">
                    <p class="font-bold mb-1">Couturier(ère)</p>
                    <p class="text-gray-500">Atelier, Planning, Sur mesure (ses commandes)</p>
                </div>
                <div class="bg-white rounded-xl p-3 border border-blue-100">
                    <p class="font-bold mb-1">Responsable Stock</p>
                    <p class="text-gray-500">Stock, Produits, Catégories, Achats fournisseurs</p>
                </div>
                <div class="bg-white rounded-xl p-3 border border-blue-100">
                    <p class="font-bold mb-1">Caissier(ère)</p>
                    <p class="text-gray-500">Ventes, Finance, Dépenses, Clients</p>
                </div>
                <div class="bg-white rounded-xl p-3 border border-blue-100">
                    <p class="font-bold mb-1">Livreur</p>
                    <p class="text-gray-500">Module Livraisons uniquement</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('users.index') }}"
               class="px-5 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                Annuler
            </a>
            <button type="submit"
                    class="px-5 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-orange-600 transition-colors inline-flex items-center gap-2">
                <i data-lucide="user-plus" style="width:15px;height:15px"></i>
                Créer l'utilisateur
            </button>
        </div>
    </form>
</div>
@endsection
