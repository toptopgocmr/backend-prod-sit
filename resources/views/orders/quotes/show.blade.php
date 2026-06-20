@extends('layouts.app')
@section('title', 'Devis ' . $quote->reference)

@section('breadcrumb')
    <a href="{{ route('quotes.index') }}" class="hover:text-gray-600 transition-colors">Devis</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">{{ $quote->reference }}</span>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Colonne principale --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- En-tête devis --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-mono text-lg font-bold text-primary">{{ $quote->reference }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Créé le {{ $quote->created_at->format('d/m/Y à H:i') }} par {{ $quote->creator->name }}</p>
                </div>
                <span class="badge-status bg-{{ $quote->getStatusColor() }}-50 text-{{ $quote->getStatusColor() }}-700 px-3 py-1.5 text-sm">
                    {{ $quote->getStatusLabel() }}
                </span>
            </div>

            {{-- Actions statut --}}
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach(\App\Models\Quote::STATUSES as $key => $info)
                    @if($key !== $quote->status)
                        <form method="POST" action="{{ route('quotes.status', $quote) }}" class="inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="{{ $key }}">
                            <button type="submit"
                                    class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-{{ $info['color'] }}-50 hover:text-{{ $info['color'] }}-700 hover:border-{{ $info['color'] }}-200 transition-colors">
                                → {{ $info['label'] }}
                            </button>
                        </form>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Client + vêtement --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-3">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4 text-purple-600"></i> Client
            </h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Nom</p>
                    <p class="font-semibold text-dark">{{ $quote->client->full_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Téléphone</p>
                    <p class="font-semibold text-dark">{{ $quote->client->phone }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Genre</p>
                    <p class="font-semibold text-dark">{{ ucfirst($quote->gender ?? '—') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Vêtement</p>
                    <p class="font-semibold text-dark">{{ ucfirst($quote->garment_type ?? '—') }}</p>
                </div>
            </div>
            @if($quote->model_name || $quote->model_description)
            <div class="pt-2 border-t border-gray-50">
                <p class="text-xs text-gray-400 mb-0.5">Modèle</p>
                <p class="text-sm font-semibold text-dark">{{ $quote->model_name }}</p>
                @if($quote->model_description)
                    <p class="text-xs text-gray-500 mt-1">{{ $quote->model_description }}</p>
                @endif
            </div>
            @endif
        </div>

        {{-- Notes --}}
        @if($quote->notes)
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="text-sm font-display font-semibold text-dark mb-2 flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4 text-purple-600"></i> Notes
            </h3>
            <p class="text-sm text-gray-600">{{ $quote->notes }}</p>
        </div>
        @endif

        {{-- Si déjà converti --}}
        @if($quote->customOrder)
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600 shrink-0"></i>
            <div>
                <p class="text-sm font-semibold text-green-800">Converti en commande</p>
                <a href="{{ route('custom-orders.show', $quote->customOrder) }}" class="text-xs text-green-600 hover:underline font-mono">
                    {{ $quote->customOrder->reference }}
                </a>
            </div>
        </div>
        @endif
    </div>

    {{-- Colonne droite : récap --}}
    <div class="space-y-5">
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden sticky top-6">
            <div class="px-5 py-4 border-b border-gray-50">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="receipt" class="w-4 h-4 text-purple-600"></i> Récapitulatif
                </h3>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Tissu</span>
                    <span class="font-semibold text-dark">{{ number_format($quote->fabric_cost, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Main d'œuvre</span>
                    <span class="font-semibold text-dark">{{ number_format($quote->labor_cost, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Accessoires</span>
                    <span class="font-semibold text-dark">{{ number_format($quote->accessories_cost, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="border-t border-gray-100 pt-3 flex justify-between">
                    <span class="font-display font-bold text-dark">TOTAL</span>
                    <span class="text-xl font-display font-bold text-purple-600">{{ number_format($quote->total, 0, ',', ' ') }} FCFA</span>
                </div>

                @if($quote->valid_until)
                <div class="text-xs text-center {{ $quote->valid_until->isPast() ? 'text-red-500' : 'text-gray-400' }} pt-1">
                    Valide jusqu'au {{ $quote->valid_until->format('d/m/Y') }}
                </div>
                @endif

                <div class="pt-3 space-y-2 border-t border-gray-50">
                    <a href="{{ route('quotes.pdf', $quote) }}" target="_blank"
                       class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                        <i data-lucide="download" style="width:15px;height:15px"></i>
                        Télécharger PDF
                    </a>

                    @if($quote->status === 'accepte' && !$quote->custom_order_id)
                    <form method="POST" action="{{ route('quotes.convert', $quote) }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-green-600 text-white text-sm font-bold hover:bg-green-700 transition-colors"
                                onclick="return confirm('Convertir ce devis en commande sur mesure ?')">
                            <i data-lucide="arrow-right-circle" style="width:15px;height:15px"></i>
                            Convertir en commande
                        </button>
                    </form>
                    @endif

                    @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('quotes.destroy', $quote) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full text-xs text-red-400 hover:text-red-600 py-1 transition-colors"
                                onclick="return confirm('Supprimer ce devis ?')">Supprimer</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
