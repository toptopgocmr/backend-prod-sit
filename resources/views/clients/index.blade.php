@extends('layouts.app')
@section('title', 'Clients')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex-1">
            <p class="text-sm text-gray-500">{{ $clients->total() }} client(s) enregistré(s)</p>
        </div>
        <a href="{{ route('clients.create') }}?mode=ancien"
           class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors shadow-sm">
            <i data-lucide="users" class="w-4 h-4"></i> Ancien client
        </a>
        <a href="{{ route('clients.create') }}"
           class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary-600 transition-colors shadow-sm">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Nouveau client
        </a>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4">
        <div class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Nom, téléphone, email..."
                   class="flex-1 min-w-48 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <select name="gender" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                <option value="">Tous sexes</option>
                <option value="homme" {{ request('gender')=='homme' ? 'selected' : '' }}>Homme</option>
                <option value="femme" {{ request('gender')=='femme' ? 'selected' : '' }}>Femme</option>
            </select>
            <select name="city" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                <option value="">Toutes villes</option>
                @foreach($cities as $city)
                    <option value="{{ $city }}" {{ request('city')==$city ? 'selected' : '' }}>{{ $city }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-dark text-white rounded-lg text-sm font-semibold">Filtrer</button>
            @if(request()->hasAny(['search','gender','city']))
                <a href="{{ route('clients.index') }}" class="px-4 py-2 border border-gray-200 text-gray-500 rounded-lg text-sm">Réinitialiser</a>
            @endif
        </div>
    </form>

    {{-- Liste --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Client</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Contact</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Sexe</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Commandes</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Total dépensé</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Inscrit le</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($clients as $client)
                        <tr class="hover:bg-surface/40 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                        <span class="text-sm font-bold text-primary">{{ strtoupper(substr($client->first_name,0,1)) }}</span>
                                    </div>
                                    <div>
                                        <a href="{{ route('clients.show', $client) }}" class="text-sm font-semibold text-dark hover:text-primary transition-colors">
                                            {{ $client->full_name }}
                                        </a>
                                        @if($client->city)
                                            <p class="text-xs text-gray-400">{{ $client->city }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-sm text-dark">{{ $client->phone }}</p>
                                @if($client->email)
                                    <p class="text-xs text-gray-400">{{ $client->email }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="text-sm">{{ $client->gender === 'homme' ? '👔' : ($client->gender === 'femme' ? '👗' : '—') }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="text-sm font-bold text-dark">{{ $client->orders_count + ($client->customOrders_count ?? 0) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-bold text-dark">{{ number_format($client->total_spent, 0, ',', ' ') }}</span>
                                <span class="text-xs text-gray-400"> FCFA</span>
                            </td>
                            <td class="px-5 py-3.5 text-right text-xs text-gray-400">
                                {{ $client->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('clients.show', $client) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark transition-colors" title="Voir">
                                        <i data-lucide="eye" style="width:15px;height:15px"></i>
                                    </a>
                                    <a href="{{ route('clients.edit', $client) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark transition-colors" title="Modifier">
                                        <i data-lucide="edit-2" style="width:15px;height:15px"></i>
                                    </a>
                                    <a href="{{ route('custom-orders.create') }}?client_id={{ $client->id }}" class="p-1.5 rounded-lg hover:bg-purple-50 text-gray-400 hover:text-purple-600 transition-colors" title="Commande sur mesure">
                                        <i data-lucide="scissors" style="width:15px;height:15px"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-gray-400">
                                <i data-lucide="users" class="w-10 h-10 mx-auto mb-3 text-gray-200"></i>
                                <p class="font-medium">Aucun client trouvé</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($clients->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">{{ $clients->links() }}</div>
        @endif
    </div>

</div>
@endsection
