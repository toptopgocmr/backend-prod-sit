@extends('layouts.app')
@section('title', 'Utilisateurs')

@section('content')
<div class="space-y-5">

    <div class="flex items-center gap-3">
        <div class="flex-1"><p class="text-sm text-gray-500">{{ $users->count() }} utilisateur(s)</p></div>
        <a href="{{ route('users.create') }}"
           class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary-600 transition-colors">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Nouvel utilisateur
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Utilisateur</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Rôle</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Statut</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Créé le</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($users as $user)
                        <tr class="hover:bg-surface/40 transition-colors {{ $user->trashed() ? 'opacity-50' : '' }}">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $user->avatar_url }}" alt="" class="w-9 h-9 rounded-full shrink-0">
                                    <div>
                                        <p class="text-sm font-semibold text-dark">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="badge-status {{ match($user->role) {
                                    'admin'         => 'bg-dark/5 text-dark',
                                    'couturier'     => 'bg-purple-50 text-purple-700',
                                    'stock_manager' => 'bg-blue-50 text-blue-700',
                                    'cashier'       => 'bg-green-50 text-green-700',
                                    'delivery'      => 'bg-orange-50 text-orange-700',
                                    default         => 'bg-gray-50 text-gray-600',
                                } }}">
                                    {{ $user->getRoleLabel() }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($user->trashed())
                                    <span class="badge-status bg-gray-50 text-gray-400">Supprimé</span>
                                @elseif($user->is_active)
                                    <span class="badge-status bg-green-50 text-green-700">Actif</span>
                                @else
                                    <span class="badge-status bg-red-50 text-red-700">Désactivé</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right text-xs text-gray-400">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="px-5 py-3.5 text-right">
                                @if(!$user->trashed())
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('users.edit', $user) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark">
                                            <i data-lucide="edit-2" style="width:15px;height:15px"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.toggle', $user) }}">
                                                @csrf @method('PUT')
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark" title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                                    <i data-lucide="{{ $user->is_active ? 'user-x' : 'user-check' }}" style="width:15px;height:15px"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
