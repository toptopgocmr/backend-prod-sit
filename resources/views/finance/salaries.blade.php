@extends('layouts.app')
@section('title', 'Gestion des salaires')

@section('breadcrumb')
    <a href="{{ route('finance.index') }}" class="hover:text-gray-600 transition-colors">Finance</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Salaires</span>
@endsection

@section('content')
<div class="space-y-5" x-data="{ showForm: false }">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <i data-lucide="wallet" class="w-5 h-5 text-primary"></i>
            </div>
            <div>
                <h2 class="font-display font-bold text-dark">Salaires</h2>
                <p class="text-xs text-gray-400">
                    {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showForm = !showForm"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-orange-600 transition-colors">
                <i data-lucide="plus" style="width:15px;height:15px"></i>
                Enregistrer un salaire
            </button>
            <a href="{{ route('finance.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                <i data-lucide="arrow-left" style="width:15px;height:15px"></i>
                Finance
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-100 text-green-700 px-4 py-3 rounded-xl text-sm font-medium">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Sélecteur période --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <form method="GET" action="{{ route('salaries.index') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Mois</label>
                <select name="month" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Année</label>
                <select name="year" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @foreach(range(date('Y'), date('Y')-3) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-dark text-white rounded-lg text-sm font-semibold">Afficher</button>
        </form>
    </div>

    {{-- KPI --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-xs text-gray-400 mb-2 font-medium">👥 Employés actifs</p>
            <p class="text-2xl font-display font-bold text-dark">{{ $employees->count() }}</p>
            <p class="text-xs text-gray-400 mt-0.5">personnes</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-xs text-gray-400 mb-2 font-medium">✅ Salaires versés</p>
            <p class="text-2xl font-display font-bold text-dark">{{ $payments->count() }}</p>
            <p class="text-xs text-gray-400 mt-0.5">ce mois</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100">
            <p class="text-xs text-gray-400 mb-2 font-medium">💰 Total versé</p>
            <p class="text-2xl font-display font-bold text-dark">{{ number_format($totalPaid, 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400 mt-0.5">FCFA</p>
        </div>
    </div>

    {{-- Formulaire ajout --}}
    <div x-show="showForm" x-transition class="bg-white rounded-2xl border border-gray-100 p-6">
        <h3 class="font-display font-bold text-dark text-sm mb-4 flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-4 h-4 text-gray-400"></i>
            Nouveau versement de salaire
        </h3>
        <form method="POST" action="{{ route('salaries.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year"  value="{{ $year }}">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                {{-- Employé --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Employé <span class="text-red-500">*</span></label>
                    <select name="employee_id" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white @error('employee_id') border-red-400 @enderror">
                        <option value="">— Choisir —</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                                ({{ match($emp->role) { 'couturier'=>'Couturier','stock_manager'=>'Stock','cashier'=>'Caissier','delivery'=>'Livraison',default=>$emp->role } }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Salaire de base --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Salaire de base (FCFA) <span class="text-red-500">*</span></label>
                    <input type="number" name="base_salary" value="{{ old('base_salary') }}" required
                           min="0" step="1" placeholder="0"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 @error('base_salary') border-red-400 @enderror">
                    @error('base_salary') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Bonus --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Bonus (FCFA)</label>
                    <input type="number" name="bonus" value="{{ old('bonus', 0) }}"
                           min="0" step="1" placeholder="0"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>

                {{-- Retenue --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Retenue (FCFA)</label>
                    <input type="number" name="deduction" value="{{ old('deduction', 0) }}"
                           min="0" step="1" placeholder="0"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>

                {{-- Méthode --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Méthode de paiement <span class="text-red-500">*</span></label>
                    <select name="payment_method" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white @error('payment_method') border-red-400 @enderror">
                        <option value="">— Choisir —</option>
                        <option value="cash"         {{ old('payment_method')=='cash'         ? 'selected' : '' }}>💵 Espèces</option>
                        <option value="mobile_money" {{ old('payment_method')=='mobile_money' ? 'selected' : '' }}>📱 Mobile Money</option>
                        <option value="virement"     {{ old('payment_method')=='virement'     ? 'selected' : '' }}>🏦 Virement</option>
                    </select>
                    @error('payment_method') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Date --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Date de versement <span class="text-red-500">*</span></label>
                    <input type="date" name="paid_at" value="{{ old('paid_at', date('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 @error('paid_at') border-red-400 @enderror">
                    @error('paid_at') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" @click="showForm = false"
                        class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit"
                        class="px-5 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-orange-600 transition-colors inline-flex items-center gap-2">
                    <i data-lucide="save" style="width:14px;height:14px"></i>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>

    {{-- Tableau des paiements --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-display font-bold text-dark text-sm">
                Versements — {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}
            </h3>
            <span class="text-xs text-gray-400">{{ $payments->count() }} versement(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-left font-semibold">Employé</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Rôle</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Base</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Bonus</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Retenue</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Net versé</th>
                        <th class="px-5 py-3.5 text-center font-semibold">Méthode</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-surface/40 transition-colors">
                        <td class="px-5 py-4">
                            <p class="text-sm font-semibold text-dark">{{ $payment->employee->name ?? '—' }}</p>
                        </td>
                        <td class="px-5 py-4 text-xs text-gray-500">
                            {{ match($payment->employee->role ?? '') {
                                'couturier'     => 'Couturier',
                                'stock_manager' => 'Stock',
                                'cashier'       => 'Caissier',
                                'delivery'      => 'Livraison',
                                default         => $payment->employee->role ?? '—'
                            } }}
                        </td>
                        <td class="px-5 py-4 text-right text-sm text-gray-600">
                            {{ number_format($payment->base_salary, 0, ',', ' ') }}
                        </td>
                        <td class="px-5 py-4 text-right text-sm {{ $payment->bonus > 0 ? 'text-green-600 font-semibold' : 'text-gray-300' }}">
                            {{ $payment->bonus > 0 ? '+'.number_format($payment->bonus, 0, ',', ' ') : '—' }}
                        </td>
                        <td class="px-5 py-4 text-right text-sm {{ $payment->deduction > 0 ? 'text-red-500 font-semibold' : 'text-gray-300' }}">
                            {{ $payment->deduction > 0 ? '-'.number_format($payment->deduction, 0, ',', ' ') : '—' }}
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-dark">
                            {{ number_format($payment->net_amount, 0, ',', ' ') }}
                            <span class="text-xs text-gray-400 font-normal">FCFA</span>
                        </td>
                        <td class="px-5 py-4 text-center text-xs text-gray-500">
                            {{ match($payment->payment_method) {
                                'cash'         => '💵 Espèces',
                                'mobile_money' => '📱 Mobile Money',
                                'virement'     => '🏦 Virement',
                                default        => $payment->payment_method
                            } }}
                        </td>
                        <td class="px-5 py-4 text-right text-xs text-gray-400">
                            {{ \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center text-gray-400">
                            <div class="w-16 h-16 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="wallet" class="w-8 h-8 text-gray-200"></i>
                            </div>
                            <p class="font-medium">Aucun salaire versé ce mois</p>
                            <p class="text-xs mt-1">Cliquez sur "Enregistrer un salaire" pour commencer.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($payments->count() > 0)
                <tfoot>
                    <tr class="bg-gray-50/50 border-t border-gray-100">
                        <td colspan="5" class="px-5 py-3.5 text-sm font-semibold text-dark text-right">Total versé :</td>
                        <td class="px-5 py-3.5 text-right text-sm font-bold text-dark">
                            {{ number_format($totalPaid, 0, ',', ' ') }}
                            <span class="text-xs text-gray-400 font-normal">FCFA</span>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection
