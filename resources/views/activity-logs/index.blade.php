@extends('layouts.app')
@section('title', 'Journal d\'activité')

@section('breadcrumb')
    <span>Admin</span>
    <span class="mx-1">›</span>
    <span class="text-dark font-semibold">Journal d'activité</span>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Stats rapides ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white rounded-2xl p-5 border border-gray-100" style="border-left:4px solid #3b82f6">
            <p class="text-xs text-gray-400 font-medium mb-1">Actions aujourd'hui</p>
            <p class="text-2xl font-bold text-dark">{{ $stats['today'] }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100" style="border-left:4px solid #10b981">
            <p class="text-xs text-gray-400 font-medium mb-1">Connexions aujourd'hui</p>
            <p class="text-2xl font-bold text-dark">{{ $stats['logins'] }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100" style="border-left:4px solid #E8820C">
            <p class="text-xs text-gray-400 font-medium mb-1">Utilisateurs actifs</p>
            <p class="text-2xl font-bold text-dark">{{ $stats['active'] }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100" style="border-left:4px solid #8b5cf6">
            <p class="text-xs text-gray-400 font-medium mb-1">Total actions</p>
            <p class="text-2xl font-bold text-dark">{{ number_format($stats['total']) }}</p>
        </div>
    </div>

    {{-- Filtres ─────────────────────────────────────────────────────── --}}
    <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4">
        <div class="flex flex-wrap gap-3">

            <select name="user_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">Tous les utilisateurs</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->getRoleLabel() }})
                    </option>
                @endforeach
            </select>

            <select name="action" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                <option value="">Toutes les actions</option>
                @foreach(['login'=>'Connexion','logout'=>'Déconnexion','created'=>'Création','updated'=>'Modification','deleted'=>'Suppression'] as $val => $label)
                    <option value="{{ $val }}" {{ request('action') == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <input type="date" name="date" value="{{ request('date') }}"
                   class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">

            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Rechercher une action..."
                   class="flex-1 min-w-48 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">

            <button type="submit" class="px-4 py-2 bg-dark text-white rounded-lg text-sm font-semibold hover:bg-dark/80 transition-colors">
                Filtrer
            </button>

            @if(request()->hasAny(['user_id','action','date','search']))
                <a href="{{ route('activity-logs.index') }}" class="px-4 py-2 border border-gray-200 text-gray-500 rounded-lg text-sm hover:bg-gray-50">
                    Réinitialiser
                </a>
            @endif
        </div>
    </form>

    {{-- Tableau ──────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Utilisateur</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Action</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Description</th>
                        <th class="px-5 py-3.5 text-left font-semibold">IP</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Date & Heure</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/50 transition-colors">

                            {{-- Utilisateur --}}
                            <td class="px-5 py-3.5">
                                @if($log->user)
                                    <div class="flex items-center gap-2.5">
                                        <img src="{{ $log->user->avatar_url }}" alt=""
                                             class="w-7 h-7 rounded-full shrink-0">
                                        <div>
                                            <p class="text-sm font-semibold text-dark leading-tight">{{ $log->user->name }}</p>
                                            <p class="text-xs text-gray-400">{{ $log->user->getRoleLabel() }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Système</span>
                                @endif
                            </td>

                            {{-- Action badge --}}
                            <td class="px-5 py-3.5">
                                @php
                                    $badgeConfig = match($log->action) {
                                        'login'   => ['bg-green-50 text-green-700',  '→ Connexion'],
                                        'logout'  => ['bg-gray-100 text-gray-500',   '← Déconnexion'],
                                        'created' => ['bg-blue-50 text-blue-700',    '+ Création'],
                                        'updated' => ['bg-yellow-50 text-yellow-700','✎ Modification'],
                                        'deleted' => ['bg-red-50 text-red-700',      '✕ Suppression'],
                                        default   => ['bg-gray-50 text-gray-600',    $log->action],
                                    };
                                @endphp
                                <span class="badge-status {{ $badgeConfig[0] }}">
                                    {{ $badgeConfig[1] }}
                                </span>
                            </td>

                            {{-- Description --}}
                            <td class="px-5 py-3.5">
                                <p class="text-sm text-gray-700">{{ $log->description }}</p>
                                @if($log->model_type)
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ class_basename($log->model_type) }}
                                        @if($log->model_id) #{{ $log->model_id }} @endif
                                    </p>
                                @endif
                            </td>

                            {{-- IP --}}
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs text-gray-400">{{ $log->ip_address ?? '—' }}</span>
                            </td>

                            {{-- Date --}}
                            <td class="px-5 py-3.5 text-right">
                                <p class="text-sm text-dark font-medium">{{ $log->created_at->format('d/m/Y') }}</p>
                                <p class="text-xs text-gray-400">{{ $log->created_at->format('H:i:s') }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center text-gray-400">
                                <i data-lucide="activity" class="w-10 h-10 mx-auto mb-3 text-gray-200"></i>
                                <p class="font-medium">Aucune activité enregistrée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
