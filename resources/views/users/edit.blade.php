@extends('layouts.app')
@section('title', 'Modifier l\'utilisateur')

@section('breadcrumb')
    <a href="{{ route('users.index') }}" class="hover:text-gray-600 transition-colors">Utilisateurs</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">{{ $user->name }}</span>
@endsection

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ $user->avatar_url }}" alt="" class="w-12 h-12 rounded-xl shrink-0">
            <div>
                <h2 class="font-display font-bold text-dark">{{ $user->name }}</h2>
                <p class="text-xs text-gray-400">{{ $user->email }}</p>
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
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            <h3 class="font-display font-bold text-dark text-sm flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-gray-400"></i>
                Informations du compte
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-dark mb-1.5">Nom complet <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-dark mb-1.5">Adresse email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-dark mb-1.5">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-dark mb-1.5">Rôle <span class="text-red-500">*</span></label>
                    <select name="role" required
                            class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white transition-all">
                        <option value="admin"         {{ old('role', $user->role)=='admin'         ? 'selected' : '' }}>Administrateur</option>
                        <option value="couturier"     {{ old('role', $user->role)=='couturier'     ? 'selected' : '' }}>Couturier(ère)</option>
                        <option value="stock_manager" {{ old('role', $user->role)=='stock_manager' ? 'selected' : '' }}>Responsable Stock</option>
                        <option value="cashier"       {{ old('role', $user->role)=='cashier'       ? 'selected' : '' }}>Caissier(ère)</option>
                        <option value="delivery"      {{ old('role', $user->role)=='delivery'      ? 'selected' : '' }}>Livreur</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-2">Changer le mot de passe (laisser vide pour conserver)</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-dark mb-1.5">Nouveau mot de passe</label>
                    <input type="password" name="password" minlength="8"
                           placeholder="Minimum 8 caractères"
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-dark mb-1.5">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation"
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all">
                </div>
            </div>
        </div>

        <div class="flex justify-between">
            @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Confirmer la suppression de {{ $user->name }} ?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-5 py-2.5 bg-red-50 text-red-600 border border-red-200 rounded-xl text-sm font-semibold hover:bg-red-100 transition-colors inline-flex items-center gap-2">
                    <i data-lucide="trash-2" style="width:15px;height:15px"></i>
                    Supprimer
                </button>
            </form>
            @else
            <div></div>
            @endif
            <div class="flex gap-3">
                <a href="{{ route('users.index') }}"
                   class="px-5 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                    Annuler
                </a>
                <button type="submit"
                        class="px-5 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-orange-600 transition-colors inline-flex items-center gap-2">
                    <i data-lucide="save" style="width:15px;height:15px"></i>
                    Sauvegarder
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
