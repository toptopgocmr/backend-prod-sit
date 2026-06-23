@extends('layouts.app')
@section('title', 'Modifier la dépense')

@section('breadcrumb')
    <a href="{{ route('expenses.index') }}" class="hover:text-gray-600 transition-colors">Dépenses</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Modifier</span>
@endsection

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <i data-lucide="edit-2" class="w-5 h-5 text-primary"></i>
            </div>
            <div>
                <h2 class="font-display font-bold text-dark">Modifier la dépense</h2>
                <p class="text-xs text-gray-400">{{ $expense->label }}</p>
            </div>
        </div>
        <a href="{{ route('expenses.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
            <i data-lucide="arrow-left" style="width:15px;height:15px"></i>
            Retour
        </a>
    </div>

    <form method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            <h3 class="font-display font-bold text-dark text-sm flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-gray-400"></i>
                Informations générales
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Libellé --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-dark mb-1.5">
                        Libellé <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="label" value="{{ old('label', $expense->label) }}" required
                           placeholder="Ex: Achat tissu wax, Loyer atelier, Salaire..."
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all @error('label') border-red-400 @enderror">
                    @error('label') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Catégorie --}}
                <div>
                    <label class="block text-sm font-semibold text-dark mb-1.5">
                        Catégorie <span class="text-red-500">*</span>
                    </label>
                    <select name="expense_category_id" required
                            class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all bg-white @error('expense_category_id') border-red-400 @enderror">
                        <option value="">— Choisir une catégorie —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('expense_category_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Montant --}}
                <div>
                    <label class="block text-sm font-semibold text-dark mb-1.5">
                        Montant (FCFA) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}" required
                           min="1" step="1"
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all @error('amount') border-red-400 @enderror">
                    @error('amount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Méthode de paiement --}}
                <div>
                    <label class="block text-sm font-semibold text-dark mb-1.5">
                        Méthode de paiement <span class="text-red-500">*</span>
                    </label>
                    <select name="payment_method" required
                            class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all bg-white">
                        <option value="cash"         {{ old('payment_method', $expense->payment_method)=='cash'         ? 'selected' : '' }}>Espèces</option>
                        <option value="mobile_money" {{ old('payment_method', $expense->payment_method)=='mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                        <option value="virement"     {{ old('payment_method', $expense->payment_method)=='virement'     ? 'selected' : '' }}>Virement</option>
                        <option value="credit"       {{ old('payment_method', $expense->payment_method)=='credit'       ? 'selected' : '' }}>Crédit</option>
                    </select>
                </div>

                {{-- Date --}}
                <div>
                    <label class="block text-sm font-semibold text-dark mb-1.5">
                        Date de la dépense <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="expense_date"
                           value="{{ old('expense_date', \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d')) }}"
                           required
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all">
                </div>

                {{-- Référence --}}
                <div>
                    <label class="block text-sm font-semibold text-dark mb-1.5">Référence / N° facture</label>
                    <input type="text" name="reference" value="{{ old('reference', $expense->reference) }}"
                           placeholder="Ex: FACT-2026-001"
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all">
                </div>

                {{-- Notes --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-dark mb-1.5">Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all resize-none">{{ old('notes', $expense->notes) }}</textarea>
                </div>

            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('expenses.index') }}"
               class="px-5 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                Annuler
            </a>
            <button type="submit"
                    class="px-5 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-orange-600 transition-colors inline-flex items-center gap-2">
                <i data-lucide="save" style="width:15px;height:15px"></i>
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
@endsection
