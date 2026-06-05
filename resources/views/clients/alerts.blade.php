@extends('layouts.app')
@section('title', 'Alertes clients')

@section('breadcrumb')
    <a href="{{ route('clients.index') }}" class="hover:text-gray-600 transition-colors">Clients</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Alertes</span>
@endsection

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-2xl bg-orange-50 flex items-center justify-center">
                <i data-lucide="bell" class="w-5 h-5 text-primary"></i>
            </div>
            <div>
                <h2 class="text-lg font-display font-bold text-dark">Alertes & Signalements</h2>
                <p class="text-xs text-gray-400">Anniversaires, clients inactifs et fidélité</p>
            </div>
        </div>
        <a href="{{ route('clients.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Retour
        </a>
    </div>

    {{-- Stats résumé --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-pink-50 rounded-2xl border border-pink-100 p-5">
            <div class="flex items-center gap-3 mb-1">
                <i data-lucide="cake" class="w-5 h-5 text-pink-500"></i>
                <span class="text-xs font-semibold text-pink-600 uppercase tracking-wider">Anniversaires</span>
            </div>
            <p class="text-3xl font-display font-bold text-pink-700">{{ $birthdays->count() }}</p>
            <p class="text-xs text-pink-500 mt-1">dans les 30 prochains jours</p>
        </div>
        <div class="bg-amber-50 rounded-2xl border border-amber-100 p-5">
            <div class="flex items-center gap-3 mb-1">
                <i data-lucide="clock" class="w-5 h-5 text-amber-500"></i>
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Clients inactifs</span>
            </div>
            <p class="text-3xl font-display font-bold text-amber-700">{{ $inactive->count() }}</p>
            <p class="text-xs text-amber-500 mt-1">sans commande depuis 90+ jours</p>
        </div>
        <div class="bg-green-50 rounded-2xl border border-green-100 p-5">
            <div class="flex items-center gap-3 mb-1">
                <i data-lucide="star" class="w-5 h-5 text-green-500"></i>
                <span class="text-xs font-semibold text-green-600 uppercase tracking-wider">Meilleurs clients</span>
            </div>
            <p class="text-3xl font-display font-bold text-green-700">{{ $topClients->count() }}</p>
            <p class="text-xs text-green-500 mt-1">à récompenser en priorité</p>
        </div>
    </div>

    {{-- ── Anniversaires ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-3">
            <i data-lucide="cake" class="w-5 h-5 text-pink-500"></i>
            <h3 class="text-sm font-display font-semibold text-dark">Anniversaires — 30 prochains jours</h3>
            <span class="ml-auto text-xs bg-pink-100 text-pink-700 px-2 py-0.5 rounded-full font-bold">{{ $birthdays->count() }}</span>
        </div>

        @if($birthdays->isEmpty())
            <div class="px-5 py-10 text-center text-gray-400 text-sm">
                <i data-lucide="cake" class="w-8 h-8 mx-auto mb-2 text-gray-200"></i>
                Aucun anniversaire dans les 30 prochains jours
            </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($birthdays as $client)
            <div class="px-5 py-4 flex items-center gap-4">
                {{-- Avatar --}}
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0
                    {{ $client->days_until_birthday === 0 ? 'bg-pink-500 text-white' : 'bg-pink-100 text-pink-700' }}">
                    {{ strtoupper(substr($client->first_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-dark">{{ $client->full_name }}</p>
                    <p class="text-xs text-gray-400">{{ $client->phone }} @if($client->email)· {{ $client->email }}@endif</p>
                </div>
                {{-- Date --}}
                <div class="text-center shrink-0">
                    <p class="text-xs text-gray-400">{{ $client->next_birthday->format('d/m') }}</p>
                    @if($client->days_until_birthday === 0)
                        <span class="text-xs font-bold text-pink-600 bg-pink-50 px-2 py-0.5 rounded-full">🎂 Aujourd'hui !</span>
                    @elseif($client->days_until_birthday <= 7)
                        <span class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full">Dans {{ $client->days_until_birthday }}j</span>
                    @else
                        <span class="text-xs text-gray-500">Dans {{ $client->days_until_birthday }} jours</span>
                    @endif
                </div>
                {{-- Actions WhatsApp / Email --}}
                <div class="flex items-center gap-2 shrink-0">
                    @if($client->phone)
                    @php
                        $phone = preg_replace('/\D/', '', $client->phone);
                        $msg = urlencode("Bonjour {$client->first_name} ! 🎂 Toute l'équipe GSIT Haute Couture vous souhaite un joyeux anniversaire ! Pour fêter ça, bénéficiez d'une réduction exclusive sur votre prochaine commande. À bientôt !");
                    @endphp
                    <a href="https://wa.me/{{ $phone }}?text={{ $msg }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition-colors">
                        <i data-lucide="message-circle" style="width:13px;height:13px"></i> WhatsApp
                    </a>
                    @endif
                    @if($client->email)
                    <a href="mailto:{{ $client->email }}?subject={{ urlencode('Joyeux anniversaire de la part de GSIT Haute Couture !') }}&body={{ urlencode("Bonjour {$client->first_name},\n\nToute l'équipe GSIT Haute Couture vous souhaite un joyeux anniversaire ! 🎂\n\nPour célébrer ce jour spécial, nous vous offrons une réduction exclusive sur votre prochaine commande.\n\nÀ très bientôt !\nL'équipe GSIT") }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition-colors">
                        <i data-lucide="mail" style="width:13px;height:13px"></i> Email
                    </a>
                    @endif
                    <a href="{{ route('clients.show', $client) }}"
                       class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark transition-colors">
                        <i data-lucide="eye" style="width:15px;height:15px"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ── Clients inactifs ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500"></i>
            <h3 class="text-sm font-display font-semibold text-dark">Clients inactifs — 90+ jours sans commande</h3>
            <span class="ml-auto text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-bold">{{ $inactive->count() }}</span>
        </div>

        @if($inactive->isEmpty())
            <div class="px-5 py-10 text-center text-gray-400 text-sm">
                <i data-lucide="check-circle" class="w-8 h-8 mx-auto mb-2 text-green-200"></i>
                Tous vos clients sont actifs — bravo !
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3 text-left font-semibold">Client</th>
                        <th class="px-5 py-3 text-left font-semibold">Contact</th>
                        <th class="px-5 py-3 text-center font-semibold">Commandes</th>
                        <th class="px-5 py-3 text-right font-semibold">Total dépensé</th>
                        <th class="px-5 py-3 text-center font-semibold">Dernière activité</th>
                        <th class="px-5 py-3 text-center font-semibold">Inactivité</th>
                        <th class="px-5 py-3 text-right font-semibold">Relance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($inactive as $client)
                    <tr class="hover:bg-amber-50/30 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($client->first_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-dark">{{ $client->full_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $client->city }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm text-dark">{{ $client->phone }}</p>
                            <p class="text-xs text-gray-400">{{ $client->email ?? '—' }}</p>
                        </td>
                        <td class="px-5 py-4 text-center text-sm font-semibold text-dark">{{ $client->orders_count }}</td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-dark">
                            {{ number_format($client->total_spent, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-5 py-4 text-center text-xs text-gray-500">
                            {{ $client->last_activity ? $client->last_activity->format('d/m/Y') : 'Jamais' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($client->days_inactive)
                                <span class="text-xs font-bold {{ $client->days_inactive > 180 ? 'text-red-600 bg-red-50' : 'text-amber-600 bg-amber-50' }} px-2 py-0.5 rounded-full">
                                    {{ $client->days_inactive }}j
                                </span>
                            @else
                                <span class="text-xs text-gray-400">Nouveau</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($client->phone)
                                @php
                                    $phone = preg_replace('/\D/', '', $client->phone);
                                    $msg = urlencode("Bonjour {$client->first_name} ! 👋 Cela fait un moment que vous ne nous avez pas rendu visite chez GSIT Haute Couture. Nous avons de nouvelles créations qui pourraient vous plaire. Venez nous voir ou passez commande dès aujourd'hui !");
                                @endphp
                                <a href="https://wa.me/{{ $phone }}?text={{ $msg }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition-colors">
                                    <i data-lucide="message-circle" style="width:12px;height:12px"></i> WA
                                </a>
                                @endif
                                @if($client->email)
                                <a href="mailto:{{ $client->email }}?subject={{ urlencode('GSIT Haute Couture vous manque !') }}&body={{ urlencode("Bonjour {$client->first_name},\n\nNous espérons que vous allez bien ! Cela fait un moment que nous n'avons pas eu le plaisir de vous servir.\n\nDe nouvelles créations vous attendent chez GSIT Haute Couture. N'hésitez pas à nous contacter pour passer commande.\n\nÀ très bientôt !\nL'équipe GSIT") }}"
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition-colors">
                                    <i data-lucide="mail" style="width:12px;height:12px"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ── Meilleurs clients ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-3">
            <i data-lucide="trophy" class="w-5 h-5 text-yellow-500"></i>
            <h3 class="text-sm font-display font-semibold text-dark">Top 5 — Clients fidèles à récompenser</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($topClients as $i => $client)
            <div class="px-5 py-4 flex items-center gap-4">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shrink-0
                    {{ $i === 0 ? 'bg-yellow-400 text-white' : ($i === 1 ? 'bg-gray-300 text-white' : ($i === 2 ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-500')) }}">
                    {{ $i + 1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-dark">{{ $client->full_name }}</p>
                    <p class="text-xs text-gray-400">{{ $client->orders_count }} commande(s) · {{ $client->phone }}</p>
                </div>
                <p class="text-sm font-bold text-primary shrink-0">{{ number_format($client->total_spent, 0, ',', ' ') }} FCFA</p>
                <div class="flex items-center gap-2 shrink-0">
                    @if($client->phone)
                    @php
                        $phone = preg_replace('/\D/', '', $client->phone);
                        $msg = urlencode("Bonjour {$client->first_name} ! 🌟 Merci pour votre fidélité chez GSIT Haute Couture ! En tant que client privilégié, nous vous réservons une surprise. Contactez-nous pour en profiter !");
                    @endphp
                    <a href="https://wa.me/{{ $phone }}?text={{ $msg }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition-colors">
                        <i data-lucide="message-circle" style="width:12px;height:12px"></i> WhatsApp
                    </a>
                    @endif
                    <a href="{{ route('clients.show', $client) }}"
                       class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-dark transition-colors">
                        <i data-lucide="eye" style="width:15px;height:15px"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
