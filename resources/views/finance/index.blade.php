@extends('layouts.app')
@section('title', 'Finance')

@section('content')
<div class="space-y-5">

    {{-- Sélecteur période --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <form method="GET" action="{{ route('finance.report') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Mois</label>
                <select name="month" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ isset($report) && $report['month'] == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Année</label>
                <select name="year" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @foreach(range(date('Y'), date('Y')-3) as $y)
                        <option value="{{ $y }}" {{ isset($report) && $report['year'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold">Afficher</button>
        </form>
    </div>

    @if(isset($report))

    {{-- KPIs financiers --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-xs text-gray-400 mb-2 font-medium">💰 Revenus totaux</p>
            <p class="text-xl font-display font-bold text-dark">{{ number_format($report['revenue']['total'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-300 mt-0.5">FCFA</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-xs text-gray-400 mb-2 font-medium">📤 Dépenses totales</p>
            <p class="text-xl font-display font-bold text-red-600">{{ number_format($report['expenses']['total'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-300 mt-0.5">FCFA</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-xs text-gray-400 mb-2 font-medium">📈 Bénéfice net</p>
            <p class="text-xl font-display font-bold {{ $report['profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                {{ $report['profit'] >= 0 ? '+' : '' }}{{ number_format($report['profit'], 0, ',', ' ') }}
            </p>
            <p class="text-xs text-gray-300 mt-0.5">FCFA</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-xs text-gray-400 mb-2 font-medium">📊 Marge</p>
            <p class="text-xl font-display font-bold {{ $report['margin'] >= 30 ? 'text-emerald-600' : 'text-orange-500' }}">
                {{ $report['margin'] }}%
            </p>
            <p class="text-xs text-gray-300 mt-0.5">Rentabilité</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Revenus par source --}}
        <div class="bg-white rounded-2xl p-6 border border-gray-100">
            <h3 class="font-display font-bold text-dark mb-4">Revenus par source</h3>
            <div class="space-y-3">
                @foreach($report['sales_by_type'] as $label => $amount)
                    @php $pct = $report['revenue']['total'] > 0 ? ($amount / $report['revenue']['total']) * 100 : 0; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium text-dark">{{ $label }}</span>
                            <span class="font-bold text-dark">{{ number_format($amount, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-primary h-2 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">{{ round($pct, 1) }}%</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Dépenses par catégorie --}}
        <div class="bg-white rounded-2xl p-6 border border-gray-100">
            <h3 class="font-display font-bold text-dark mb-4">Dépenses par catégorie</h3>
            <div class="space-y-2">
                @foreach($report['expense_breakdown'] as $cat)
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full shrink-0" style="background: {{ $cat->color }}"></div>
                        <span class="flex-1 text-sm text-dark">{{ $cat->name }}</span>
                        <span class="text-sm font-bold text-dark">{{ number_format($cat->total, 0, ',', ' ') }}</span>
                    </div>
                @endforeach
                <div class="border-t pt-2 flex justify-between mt-2">
                    <span class="text-sm font-semibold text-dark">Salaires</span>
                    <span class="text-sm font-bold text-dark">{{ number_format($report['expenses']['salaries'], 0, ',', ' ') }}</span>
                </div>
                @if(isset($report['expenses']['purchases']) && $report['expenses']['purchases'] > 0)
                <div class="flex justify-between mt-1.5">
                    <span class="text-sm font-semibold text-amber-700 flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span> Achats stock
                    </span>
                    <span class="text-sm font-bold text-amber-700">{{ number_format($report['expenses']['purchases'], 0, ',', ' ') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Créances --}}
    @if($report['unpaid_receivables'] > 0)
    <div class="bg-orange-50 border border-orange-100 rounded-2xl p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
            <i data-lucide="alert-circle" class="w-5 h-5 text-orange-600"></i>
        </div>
        <div>
            <p class="font-semibold text-orange-900">Créances impayées : {{ number_format($report['unpaid_receivables'], 0, ',', ' ') }} FCFA</p>
            <p class="text-sm text-orange-700 mt-0.5">Montants encore dus par les clients sur les commandes en cours.</p>
        </div>
        <a href="{{ route('orders.index', ['payment_status' => 'not_paid']) }}" class="ml-auto text-sm font-semibold text-orange-700 hover:underline whitespace-nowrap">
            Voir les impayés →
        </a>
    </div>
    @endif

    @endif

</div>
@endsection
