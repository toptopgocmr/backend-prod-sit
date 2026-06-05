@extends('layouts.app')

@section('title', 'Nouvelle livraison')

@section('breadcrumb')
    <a href="{{ route('deliveries.index') }}" class="hover:text-primary-500 transition-colors">Livraisons</a>
    <i data-lucide="chevron-right" style="width:12px;height:12px"></i>
    <span class="text-gray-600 font-medium">Nouvelle livraison</span>
@endsection

@push('styles')
<style>
    .form-input {
        width: 100%;
        padding: 10px 14px;
        font-size: 0.875rem;
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        background: #f9fafb;
        color: #1A1A2E;
        font-family: 'Plus Jakarta Sans', sans-serif;
        transition: border-color .15s, box-shadow .15s, background .15s;
        outline: none;
    }
    .form-input:focus {
        border-color: #E8820C;
        background: #fff;
        box-shadow: 0 0 0 3px #E8820C22;
    }
    .form-input::placeholder { color: #9ca3af; }
    .form-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #6b7280;
        margin-bottom: 6px;
    }
    .type-card {
        flex: 1;
        border: 1.5px solid #e5e7eb;
        border-radius: 14px;
        padding: 16px;
        cursor: pointer;
        transition: border-color .15s, background .15s, box-shadow .15s;
        background: #f9fafb;
        position: relative;
    }
    .type-card:has(input:checked) {
        border-color: #E8820C;
        background: #FEF3E2;
        box-shadow: 0 0 0 3px #E8820C18;
    }
    .type-card input[type="radio"] { position: absolute; opacity: 0; }
    .type-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 10px;
    }
    .section-card {
        background: #fff;
        border: 1px solid #f3f4f6;
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 16px;
    }
    .section-title {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #9ca3af;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #f3f4f6;
    }
    @keyframes fadeSlide { from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none} }
    .fade-in { animation: fadeSlide .2s ease both; }
    .fade-in:nth-child(2){animation-delay:.06s}
    .fade-in:nth-child(3){animation-delay:.12s}
    .fade-in:nth-child(4){animation-delay:.18s}
    #address-block { transition: opacity .2s, max-height .2s; overflow: hidden; }
</style>
@endpush

@section('content')

<div class="max-w-2xl mx-auto">

    {{-- En-tête --}}
    <div class="flex items-center justify-between mb-6 fade-in">
        <div>
            <h2 class="text-xl font-bold text-dark font-display">Nouvelle livraison</h2>
            <p class="text-sm text-gray-400 mt-0.5">Remplissez les informations ci-dessous</p>
        </div>
        <a href="{{ route('deliveries.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-3 py-2 rounded-xl transition-colors">
            <i data-lucide="arrow-left" style="width:14px;height:14px"></i>
            Retour
        </a>
    </div>

    {{-- Erreurs --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-100 rounded-2xl px-4 py-3 mb-5 flex gap-3 fade-in">
        <i data-lucide="alert-circle" style="width:18px;height:18px;color:#dc2626;flex-shrink:0;margin-top:1px"></i>
        <ul class="text-sm text-red-700 space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('deliveries.store') }}" id="deliveryForm">
        @csrf

        {{-- ── Section 1 : Client ────────────────────────────────── --}}
        <div class="section-card fade-in">
            <p class="section-title"><i data-lucide="user" style="width:14px;height:14px"></i>Client</p>

            <div>
                <label class="form-label">Client <span class="text-red-400">*</span></label>
                <div class="relative">
                    <i data-lucide="search" style="width:15px;height:15px;position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9ca3af;pointer-events:none"></i>
                    <select name="client_id" required
                            class="form-input pl-9 @error('client_id') !border-red-300 !bg-red-50 @enderror"
                            style="padding-left:36px">
                        <option value="">Sélectionner un client…</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>
                                {{ $client->first_name }} {{ $client->last_name }}{{ $client->phone ? ' — '.$client->phone : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('client_id')
                    <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                        <i data-lucide="alert-circle" style="width:12px;height:12px"></i>{{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        {{-- ── Section 2 : Type de livraison ─────────────────────── --}}
        <div class="section-card fade-in">
            <p class="section-title"><i data-lucide="map-pin" style="width:14px;height:14px"></i>Type de livraison</p>

            <div class="flex gap-3 mb-5">
                {{-- Livraison --}}
                <label class="type-card">
                    <input type="radio" name="type" value="livraison"
                           @checked(old('type','livraison') === 'livraison') id="type_livraison">
                    <div class="type-icon bg-blue-50">
                        <i data-lucide="truck" style="width:20px;height:20px;color:#2563eb"></i>
                    </div>
                    <p class="font-semibold text-sm text-dark">Livraison à domicile</p>
                    <p class="text-xs text-gray-400 mt-0.5">Expédition à l'adresse du client</p>
                </label>

                {{-- Retrait --}}
                <label class="type-card">
                    <input type="radio" name="type" value="retrait_boutique"
                           @checked(old('type') === 'retrait_boutique') id="type_retrait">
                    <div class="type-icon bg-amber-50">
                        <i data-lucide="store" style="width:20px;height:20px;color:#d97706"></i>
                    </div>
                    <p class="font-semibold text-sm text-dark">Retrait en boutique</p>
                    <p class="text-xs text-gray-400 mt-0.5">Le client récupère sur place</p>
                </label>
            </div>

            {{-- Adresse --}}
            <div id="address-block">
                <label class="form-label">Adresse de livraison</label>
                <div class="relative">
                    <i data-lucide="map-pin" style="width:15px;height:15px;position:absolute;left:12px;top:12px;color:#9ca3af;pointer-events:none"></i>
                    <textarea name="delivery_address" rows="2"
                              class="form-input @error('delivery_address') !border-red-300 !bg-red-50 @enderror"
                              style="padding-left:36px;resize:none"
                              placeholder="Quartier, avenue, repère…">{{ old('delivery_address') }}</textarea>
                </div>
                @error('delivery_address')
                    <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                        <i data-lucide="alert-circle" style="width:12px;height:12px"></i>{{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        {{-- ── Section 3 : Livreur & Frais ───────────────────────── --}}
        <div class="section-card fade-in">
            <p class="section-title"><i data-lucide="settings" style="width:14px;height:14px"></i>Détails</p>

            <div class="grid grid-cols-2 gap-4">
                {{-- Livreur --}}
                <div>
                    <label class="form-label">Livreur assigné</label>
                    <div class="relative">
                        <i data-lucide="user-check" style="width:15px;height:15px;position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9ca3af;pointer-events:none"></i>
                        <select name="driver_id" class="form-input" style="padding-left:36px">
                            <option value="">Assigner plus tard</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" @selected(old('driver_id') == $driver->id)>
                                    {{ $driver->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Frais --}}
                <div>
                    <label class="form-label">Frais de livraison</label>
                    <div class="relative flex">
                        <input type="number" name="delivery_fee" min="0" step="100" placeholder="0"
                               value="{{ old('delivery_fee') }}"
                               class="form-input rounded-r-none @error('delivery_fee') !border-red-300 !bg-red-50 @enderror"
                               style="border-right:none">
                        <span class="px-3 flex items-center text-xs font-semibold text-gray-500 bg-gray-100 border-1.5 border-gray-200 rounded-r-xl border border-l-0"
                              style="border:1.5px solid #e5e7eb;border-left:none;border-radius:0 12px 12px 0;white-space:nowrap">
                            FCFA
                        </span>
                    </div>
                    @error('delivery_fee')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ── Section 4 : Notes ─────────────────────────────────── --}}
        <div class="section-card fade-in">
            <p class="section-title"><i data-lucide="file-text" style="width:14px;height:14px"></i>Notes <span class="normal-case font-normal text-gray-300 ml-1">(optionnel)</span></p>

            <div class="relative">
                <i data-lucide="message-square" style="width:15px;height:15px;position:absolute;left:12px;top:12px;color:#9ca3af;pointer-events:none"></i>
                <textarea name="notes" rows="3"
                          class="form-input @error('notes') !border-red-300 !bg-red-50 @enderror"
                          style="padding-left:36px;resize:none"
                          placeholder="Instructions spéciales, fragile, urgence, accès difficile…">{{ old('notes') }}</textarea>
            </div>
            @error('notes')
                <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                    <i data-lucide="alert-circle" style="width:12px;height:12px"></i>{{ $message }}
                </p>
            @enderror
        </div>

        {{-- ── Actions ───────────────────────────────────────────── --}}
        <div class="flex items-center justify-between pt-2 pb-6 fade-in">
            <a href="{{ route('deliveries.index') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:border-gray-300 px-4 py-2.5 rounded-xl transition-colors font-medium">
                <i data-lucide="x" style="width:14px;height:14px"></i>
                Annuler
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors shadow-sm shadow-primary-500/30">
                <i data-lucide="check" style="width:16px;height:16px"></i>
                Créer la livraison
            </button>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();

        const radios = document.querySelectorAll('input[name="type"]');
        const block  = document.getElementById('address-block');

        function toggle() {
            const val = document.querySelector('input[name="type"]:checked')?.value;
            if (val === 'retrait_boutique') {
                block.style.maxHeight = '0';
                block.style.opacity   = '0';
            } else {
                block.style.maxHeight = '200px';
                block.style.opacity   = '1';
            }
        }

        radios.forEach(r => r.addEventListener('change', toggle));
        toggle();
    });
</script>
@endpush
