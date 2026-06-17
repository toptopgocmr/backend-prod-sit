@extends('layouts.app')
@section('title', 'Commande ' . $customOrder->reference)

@section('breadcrumb')
    <a href="{{ route('custom-orders.index') }}" class="hover:text-gray-600 transition-colors">Sur mesure</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">{{ $customOrder->reference }}</span>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ══════ COLONNE GAUCHE ══════ --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- En-tête --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-2xl bg-purple-50 flex items-center justify-center">
                    <i data-lucide="scissors" class="w-5 h-5 text-purple-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-display font-bold text-dark font-mono">{{ $customOrder->reference }}</h2>
                    <p class="text-xs text-gray-400">Créée le {{ $customOrder->created_at->format('d/m/Y à H:i') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="badge-status bg-{{ $customOrder->getStatusColor() }}-50 text-{{ $customOrder->getStatusColor() }}-700">
                    {{ $customOrder->getStatusLabel() }}
                </span>
                {{-- Partage reçu --}}
                @include('partials.share-receipt', ['order' => $customOrder, 'type' => 'custom'])
                {{-- Voir la fiche --}}
                <a href="{{ route('custom-orders.fiche', $customOrder) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-purple-200 bg-purple-50 text-sm font-semibold text-purple-700 hover:bg-purple-100 transition-colors">
                    <i data-lucide="eye" class="w-4 h-4"></i> Voir la fiche
                </a>
                {{-- Télécharger PDF --}}
                <a href="{{ route('custom-orders.fiche', array_merge([$customOrder->id], ['download' => 1])) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                    <i data-lucide="download" class="w-4 h-4"></i> Télécharger PDF
                </a>
                {{-- Modifier --}}
                @if(auth()->user()->isAdmin())
                <a href="{{ route('custom-orders.edit', $customOrder) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition-colors">
                    <i data-lucide="edit-2" class="w-4 h-4"></i> Modifier
                </a>
                {{-- Supprimer --}}
                <form method="POST" action="{{ route('custom-orders.destroy', $customOrder) }}"
                      onsubmit="return confirm('Supprimer définitivement cette commande ?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-red-200 bg-red-50 text-sm font-semibold text-red-600 hover:bg-red-100 transition-colors">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Client + Modèle --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Client</h3>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-sm shrink-0">
                            {{ strtoupper(substr($customOrder->client->full_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-dark">{{ $customOrder->client->full_name }}</p>
                            <p class="text-xs text-gray-400">{{ $customOrder->client->phone }}</p>
                            <span class="text-xs bg-purple-50 text-purple-700 px-2 py-0.5 rounded-full font-semibold">{{ ucfirst($customOrder->gender) }}</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Modèle</h3>
                    <p class="text-sm font-bold text-dark">{{ ucfirst($customOrder->garment_type) }}</p>
                    @if($customOrder->model_name)
                        <p class="text-sm text-gray-600 mt-0.5">{{ $customOrder->model_name }}</p>
                    @endif
                    @if($customOrder->model_description)
                        <p class="text-xs text-gray-400 mt-2">{{ $customOrder->model_description }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tissu --}}
        @if($customOrder->fabric || $customOrder->fabric_color)
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2 mb-4">
                <i data-lucide="layers" class="w-4 h-4 text-purple-600"></i> Tissu
            </h3>
            <div class="grid grid-cols-3 gap-4">
                @if($customOrder->fabric)
                <div>
                    <p class="text-xs text-gray-400 mb-1">Tissu</p>
                    <p class="text-sm font-semibold text-dark">{{ $customOrder->fabric->name }}</p>
                    <p class="text-xs text-gray-400">{{ $customOrder->fabric->reference }}</p>
                </div>
                @endif
                @if($customOrder->fabric_meters)
                <div>
                    <p class="text-xs text-gray-400 mb-1">Mètres</p>
                    <p class="text-sm font-bold text-dark">{{ $customOrder->fabric_meters }} m</p>
                </div>
                @endif
                @if($customOrder->fabric_color)
                <div>
                    <p class="text-xs text-gray-400 mb-1">Couleur</p>
                    <p class="text-sm font-semibold text-dark">{{ $customOrder->fabric_color }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Accessoires --}}
        @if($customOrder->accessories && count($customOrder->accessories) > 0)
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2 mb-4">
                <i data-lucide="tag" class="w-4 h-4 text-purple-600"></i> Accessoires
            </h3>
            <div class="space-y-2">
                @foreach($customOrder->accessories as $acc)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <span class="text-sm text-dark">{{ $acc['name'] ?? '—' }}</span>
                    <div class="flex items-center gap-4">
                        <span class="text-xs text-gray-400">x{{ $acc['qty'] ?? 1 }}</span>
                        <span class="text-sm font-semibold text-dark">{{ number_format(($acc['price'] ?? 0) * ($acc['qty'] ?? 1), 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Historique statuts --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2 mb-4">
                <i data-lucide="clock" class="w-4 h-4 text-purple-600"></i> Historique
            </h3>

            {{-- Changer statut --}}
            <form method="POST" action="{{ route('custom-orders.status', $customOrder) }}" class="flex gap-2 mb-5 pb-5 border-b border-gray-50">
                @csrf @method('PUT')
                <select name="status" class="flex-1 px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                    @foreach(\App\Models\CustomOrder::STATUSES as $key => $info)
                        <option value="{{ $key }}" {{ $customOrder->status === $key ? 'selected' : '' }}>{{ $info['label'] }}</option>
                    @endforeach
                </select>
                <input type="text" name="comment" placeholder="Commentaire (optionnel)"
                       class="flex-1 px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition-colors">
                    Mettre à jour
                </button>
            </form>

            <div class="space-y-3">
                @foreach($customOrder->statusHistory as $log)
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center shrink-0 mt-0.5">
                        <i data-lucide="git-commit" style="width:12px;height:12px" class="text-purple-600"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="badge-status bg-{{ \App\Models\CustomOrder::STATUSES[$log->status]['color'] ?? 'gray' }}-50 text-{{ \App\Models\CustomOrder::STATUSES[$log->status]['color'] ?? 'gray' }}-700 text-xs">
                                {{ \App\Models\CustomOrder::STATUSES[$log->status]['label'] ?? $log->status }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                            <span class="text-xs text-gray-400">— {{ $log->user?->name ?? 'Système' }}</span>
                        </div>
                        @if($log->comment)
                            <p class="text-xs text-gray-500 mt-1">{{ $log->comment }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Notes --}}
        @if($customOrder->notes)
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2 mb-3">
                <i data-lucide="file-text" class="w-4 h-4 text-purple-600"></i> Notes internes
            </h3>
            <p class="text-sm text-gray-600">{{ $customOrder->notes }}</p>
        </div>
        @endif
    </div>

    {{-- ══════ COLONNE DROITE ══════ --}}
    <div class="space-y-5">

        {{-- Coûts --}}
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                    <i data-lucide="receipt" class="w-4 h-4 text-purple-600"></i> Récapitulatif
                </h3>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Tissu</span>
                    <span class="font-semibold text-dark">{{ number_format($customOrder->fabric_cost, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Main d'œuvre</span>
                    <span class="font-semibold text-dark">{{ number_format($customOrder->labor_cost, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Accessoires</span>
                    <span class="font-semibold text-dark">{{ number_format($customOrder->accessories_cost, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-100">
                    <span class="font-display font-bold text-dark">TOTAL</span>
                    <span class="text-xl font-display font-bold text-purple-600">{{ number_format($customOrder->total, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Acompte versé</span>
                    <span class="font-semibold text-green-600">{{ number_format($customOrder->amount_paid, 0, ',', ' ') }} FCFA</span>
                </div>
                @if($customOrder->balance > 0)
                <div class="flex justify-between px-3 py-2.5 rounded-xl bg-orange-50 border border-orange-100">
                    <span class="text-xs font-semibold text-orange-700">Reste à payer</span>
                    <span class="text-sm font-bold text-orange-700">{{ number_format($customOrder->balance, 0, ',', ' ') }} FCFA</span>
                </div>
                @endif
                <div class="flex justify-center pt-1">
                    <span class="badge-status {{ match($customOrder->payment_status) { 'paid'=>'bg-green-50 text-green-700','partial'=>'bg-yellow-50 text-yellow-700',default=>'bg-red-50 text-red-700' } }}">
                        {{ match($customOrder->payment_status) { 'paid'=>'✓ Soldé','partial'=>'◑ Acompte versé',default=>'○ Non payé' } }}
                    </span>
                </div>

                {{-- Enregistrer paiement --}}
                @if($customOrder->balance > 0)
                <form method="POST" action="{{ route('custom-orders.payment', $customOrder) }}" class="space-y-2 pt-2 border-t border-gray-50">
                    @csrf
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Encaisser un paiement</h4>
                    <select name="method" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                        <option value="cash">Espèces</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="card">Carte</option>
                        <option value="credit">Crédit</option>
                    </select>
                    <div class="relative">
                        <input type="number" name="amount" min="1" max="{{ $customOrder->balance }}" step="500"
                               placeholder="Montant" value="{{ $customOrder->balance }}"
                               class="w-full px-3 py-2 pr-16 rounded-xl border border-gray-200 text-sm font-bold text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">FCFA</span>
                    </div>
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-purple-600 text-white text-sm font-bold hover:bg-purple-700 transition-colors">
                        Enregistrer le paiement
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Production --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-3">
            <h3 class="text-sm font-display font-semibold text-dark flex items-center gap-2">
                <i data-lucide="user-check" class="w-4 h-4 text-purple-600"></i> Production
            </h3>
            <div>
                <p class="text-xs text-gray-400 mb-1">Couturier</p>
                @if($customOrder->couturier)
                    <div class="flex items-center gap-2">
                        <img src="{{ $customOrder->couturier->avatar_url }}" class="w-7 h-7 rounded-full">
                        <span class="text-sm font-semibold text-dark">{{ $customOrder->couturier->name }}</span>
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">Non assigné</p>
                @endif
            </div>
            {{-- Assigner couturier --}}
            <form method="POST" action="{{ route('custom-orders.assign', $customOrder) }}" class="flex gap-2">
                @csrf @method('PUT')
                <select name="couturier_id" class="flex-1 px-2 py-1.5 rounded-lg border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                    <option value="">— Choisir —</option>
                    @foreach($couturiers as $c)
                        <option value="{{ $c->id }}" {{ $customOrder->assigned_to == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-purple-100 text-purple-700 text-xs font-semibold hover:bg-purple-200 transition-colors">
                    Assigner
                </button>
            </form>
            @if($customOrder->delivery_date)
            <div>
                <p class="text-xs text-gray-400 mb-1">Date de livraison</p>
                <p class="text-sm font-semibold {{ $customOrder->delivery_date->isPast() && !in_array($customOrder->status, ['livre','annule']) ? 'text-red-600' : 'text-dark' }}">
                    {{ $customOrder->delivery_date->format('d/m/Y') }}
                    <span class="text-xs font-normal text-gray-400 ml-1">{{ $customOrder->delivery_date->diffForHumans() }}</span>
                </p>
            </div>
            @endif
        </div>

        {{-- Progression --}}
        @if($customOrder->status !== 'annule')
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="text-sm font-display font-semibold text-dark mb-3">Progression</h3>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="bg-purple-600 h-2 rounded-full transition-all" style="width: {{ $customOrder->progress_percent }}%"></div>
            </div>
            <p class="text-xs text-gray-400 text-center mt-2">{{ $customOrder->progress_percent }}%</p>
        </div>
        @endif

    </div>
</div>
@endsection
