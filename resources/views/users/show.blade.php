@extends('layouts.app')
@section('title', 'Profil utilisateur')
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <img src="{{ $user->avatar_url }}" class="w-16 h-16 rounded-2xl" alt="">
            <div>
                <h2 class="font-display font-bold text-dark text-lg">{{ $user->name }}</h2>
                <p class="text-sm text-gray-400">{{ $user->email }}</p>
                <span class="badge-status mt-1 {{ match($user->role) {
                    'admin'         => 'bg-dark/5 text-dark',
                    'couturier'     => 'bg-purple-50 text-purple-700',
                    'stock_manager' => 'bg-blue-50 text-blue-700',
                    'cashier'       => 'bg-green-50 text-green-700',
                    'delivery'      => 'bg-orange-50 text-orange-700',
                    default         => 'bg-gray-50 text-gray-600',
                } }}">{{ $user->getRoleLabel() }}</span>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-orange-600 transition-colors">
                <i data-lucide="edit-2" style="width:15px;height:15px"></i> Modifier
            </a>
            <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                <i data-lucide="arrow-left" style="width:15px;height:15px"></i> Retour
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-5">
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Statut</p>
                @if($user->is_active)
                    <span class="badge-status bg-green-50 text-green-700">Actif</span>
                @else
                    <span class="badge-status bg-red-50 text-red-700">Désactivé</span>
                @endif
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Téléphone</p>
                <p class="text-sm text-dark">{{ $user->phone ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Membre depuis</p>
                <p class="text-sm text-dark">{{ $user->created_at->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
