@extends('layouts.app')
@section('title', 'Commandes Sur Mesure')

@section('breadcrumb')
    <span>Atelier</span>
    <span class="mx-1">›</span>
    <span class="text-dark font-semibold">Sur Mesure</span>
@endsection

@section('content')
<div class="space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex-1">
            <p class="text-sm text-gray-500">{{ $orders->total() }} commande(s) trouvée(s)</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('custom-orders.export', array_merge(request()->query(), ['format'=>'excel'])) }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition-colors">
                <i data-lucide="table-2" class="w-4 h-4"></i> Excel
            </a>
            <a href="{{ route('custom-orders.export', array_merge(request()->query(), ['format'=>'pdf'])) }}"
               target="_blank"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-red-50 hover:border-red-300 hover:text-red-700 transition-colors">
                <i data-lucide="file-text" class="w-4 h-4"></i> PDF
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('custom-orders.corbeille') }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-red-200 text-sm font-semibold text-red-500 hover:bg-red-50 transition-colors">
                <i data-lucide="trash-2" class="w-4 h-4"></i> Corbeille
            </a>
            @endif
            <a href="{{ route('quotes.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-blue-200 bg-blue-50 text-sm font-semibold text-blue-700 hover:bg-blue-100 transition-colors">
                <i data-lucide="file-text" class="w-4 h-4"></i> Nouveau devis
            </a>
            <a href="{{ route('custom-orders.create') }}"
               class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary-600 transition-colors shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i> Nouvelle commande
            </a>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4">
        <div class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Référence, client, téléphone..."
                   class="flex-1 min-w-48 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                <option value="">Tous les statuts</option>
                @foreach($statuses as $key => $info)
                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $info['label'] }}</option>
                @endforeach
            </select>
            <select name="couturier" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                <option value="">Tous les couturiers</option>
                @foreach($couturiers as $c)
                    <option value="{{ $c->id }}" {{ request('couturier') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-dark text-white rounded-lg text-sm font-semibold hover:bg-dark/80 transition-colors">Filtrer</button>
            @if(request()->hasAny(['search','status','couturier']))
                <a href="{{ route('custom-orders.index') }}" class="px-4 py-2 border border-gray-200 text-gray-500 rounded-lg text-sm hover:bg-gray-50 transition-colors">Réinitialiser</a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Référence</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Client</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Vêtement</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Couturier</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Progression</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Total</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Statut</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Livraison</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                        <tr class="hover:bg-surface/40 transition-colors">
                            <td class="px-5 py-4">
                                <a href="{{ route('custom-orders.show', $order) }}" class="font-mono text-sm font-bold text-primary hover:underline">{{ $order->reference }}</a>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-dark">{{ $order->client->full_name }}</p>
                                <p class="text-xs text-gray-400">{{ $order->client->phone }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm text-dark">{{ ucfirst($order->garment_type) }}</p>
                                <p class="text-xs text-gray-400">{{ ucfirst($order->gender) }}</p>
                            </td>
                            <td class="px-5 py-4">
                                @if($order->couturier)
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $order->couturier->avatar_url }}" class="w-6 h-6 rounded-full">
                                        <span class="text-sm text-dark">{{ $order->couturier->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Non assigné</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 w-32">
                                @if($order->status !== 'annule')
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="bg-primary h-1.5 rounded-full transition-all" style="width: {{ $order->progress_percent }}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-400 text-center mt-1">{{ $order->progress_percent }}%</p>
                                @else
                                    <span class="text-xs text-red-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <p class="text-sm font-bold text-dark">{{ number_format($order->total, 0, ',', ' ') }}</p>
                                <p class="text-xs {{ $order->balance > 0 ? 'text-orange-500' : 'text-green-500' }} font-medium">
                                    {{ $order->balance > 0 ? 'Reste ' . number_format($order->balance, 0, ',', ' ') : 'Soldé' }}
                                </p>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="badge-status bg-{{ $order->getStatusColor() }}-50 text-{{ $order->getStatusColor() }}-700">{{ $order->getStatusLabel() }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($order->delivery_date)
                                    <p class="text-xs font-medium {{ $order->delivery_date->isPast() && !in_array($order->status, ['livre','annule']) ? 'text-red-600' : 'text-gray-600' }}">{{ $order->delivery_date->format('d/m/Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ $order->delivery_date->diffForHumans() }}</p>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('custom-orders.show', $order) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark transition-colors" title="Voir">
                                        <i data-lucide="eye" style="width:15px;height:15px"></i>
                                    </a>
                                    @if(auth()->user()->isAdmin())
                                    <a href="{{ route('custom-orders.edit', $order) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark transition-colors" title="Modifier">
                                        <i data-lucide="edit-2" style="width:15px;height:15px"></i>
                                    </a>
                                    @endif
                                    <a href="{{ route('custom-orders.fiche', $order) }}" target="_blank" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark transition-colors" title="Fiche atelier">
                                        <i data-lucide="printer" style="width:15px;height:15px"></i>
                                    </a>
                                    @if(auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('custom-orders.destroy', $order) }}" onsubmit="return confirm('Supprimer cette commande ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors" title="Supprimer">
                                            <i data-lucide="trash-2" style="width:15px;height:15px"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-16 text-center text-gray-400">
                                <i data-lucide="scissors" class="w-10 h-10 mx-auto mb-3 text-gray-200"></i>
                                <p class="font-medium">Aucune commande sur mesure</p>
                                <p class="text-sm mt-1">Créez votre première commande</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">{{ $orders->links() }}</div>
        @endif
    </div>

</div>
@endsection
