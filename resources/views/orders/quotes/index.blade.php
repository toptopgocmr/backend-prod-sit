@extends('layouts.app')
@section('title', 'Devis')

@section('breadcrumb')
    <span>Atelier</span>
    <span class="mx-1">›</span>
    <span class="text-dark font-semibold">Devis</span>
@endsection

@section('content')
<div class="space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex-1">
            <p class="text-sm text-gray-500">{{ $quotes->total() }} devis trouvé(s)</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('quotes.create') }}"
               class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary-600 transition-colors shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i> Nouveau devis
            </a>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4">
        <div class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Référence, client, téléphone..."
                   class="flex-1 min-w-48 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                <option value="">Tous les statuts</option>
                @foreach($statuses as $key => $info)
                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $info['label'] }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-dark text-white rounded-lg text-sm font-semibold">Filtrer</button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('quotes.index') }}" class="px-4 py-2 border border-gray-200 text-gray-500 rounded-lg text-sm">Réinitialiser</a>
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
                        <th class="px-5 py-3.5 text-right font-semibold">Total</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Statut</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Valide jusqu'au</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($quotes as $quote)
                        <tr class="hover:bg-surface/40 transition-colors">
                            <td class="px-5 py-4">
                                <a href="{{ route('quotes.show', $quote) }}" class="font-mono text-sm font-bold text-primary hover:underline">
                                    {{ $quote->reference }}
                                </a>
                                @if($quote->custom_order_id)
                                    <span class="ml-2 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Converti</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-dark">{{ $quote->client->full_name }}</p>
                                <p class="text-xs text-gray-400">{{ $quote->client->phone }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm text-dark">{{ ucfirst($quote->garment_type ?? '—') }}</p>
                                <p class="text-xs text-gray-400">{{ $quote->model_name }}</p>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <span class="text-sm font-bold text-dark">{{ number_format($quote->total, 0, ',', ' ') }} FCFA</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="badge-status bg-{{ $quote->getStatusColor() }}-50 text-{{ $quote->getStatusColor() }}-700">
                                    {{ $quote->getStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($quote->valid_until)
                                    <span class="text-xs {{ $quote->valid_until->isPast() ? 'text-red-500' : 'text-gray-500' }}">
                                        {{ $quote->valid_until->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('quotes.show', $quote) }}"
                                       class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark transition-colors" title="Voir">
                                        <i data-lucide="eye" style="width:15px;height:15px"></i>
                                    </a>
                                    <a href="{{ route('quotes.pdf', $quote) }}" target="_blank"
                                       class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors" title="PDF">
                                        <i data-lucide="file-text" style="width:15px;height:15px"></i>
                                    </a>
                                    @if($quote->status === 'accepte' && !$quote->custom_order_id)
                                        <form method="POST" action="{{ route('quotes.convert', $quote) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="p-1.5 rounded-lg hover:bg-green-50 text-gray-400 hover:text-green-600 transition-colors"
                                                    title="Convertir en commande"
                                                    onclick="return confirm('Convertir ce devis en commande sur mesure ?')">
                                                <i data-lucide="arrow-right-circle" style="width:15px;height:15px"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-gray-400">
                                <i data-lucide="file-text" class="w-10 h-10 mx-auto mb-3 text-gray-200"></i>
                                <p class="font-medium">Aucun devis</p>
                                <p class="text-sm mt-1">Créez votre premier devis client</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotes->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">{{ $quotes->links() }}</div>
        @endif
    </div>
</div>
@endsection
