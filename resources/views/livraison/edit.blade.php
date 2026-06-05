@extends('layouts.app')

@section('title', 'Modifier — ' . $delivery->reference)

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('deliveries.index') }}">Livraisons</a></li>
            <li class="breadcrumb-item"><a href="{{ route('deliveries.show', $delivery) }}">{{ $delivery->reference }}</a></li>
            <li class="breadcrumb-item active">Modifier</li>
        </ol>
    </nav>
    <h1 class="h3 fw-bold mb-0">Modifier la livraison</h1>
    <small class="text-muted">{{ $delivery->reference }}</small>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- PUT deliveries.update (resource) --}}
                <form method="POST" action="{{ route('deliveries.update', $delivery) }}">
                    @csrf @method('PUT')

                    {{-- Type --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type"
                                       id="type_livraison" value="livraison"
                                       @checked(old('type', $delivery->type) === 'livraison')>
                                <label class="form-check-label" for="type_livraison">
                                    <i class="bi bi-truck me-1 text-info"></i> Livraison à domicile
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type"
                                       id="type_retrait" value="retrait_boutique"
                                       @checked(old('type', $delivery->type) === 'retrait_boutique')>
                                <label class="form-check-label" for="type_retrait">
                                    <i class="bi bi-shop me-1 text-warning"></i> Retrait en boutique
                                </label>
                            </div>
                        </div>
                        @error('type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Adresse --}}
                    <div class="mb-4" id="address-block">
                        <label for="delivery_address" class="form-label fw-semibold">Adresse de livraison</label>
                        <textarea name="delivery_address" id="delivery_address" rows="2"
                                  class="form-control @error('delivery_address') is-invalid @enderror"
                                  placeholder="Quartier, avenue, repère…">{{ old('delivery_address', $delivery->delivery_address) }}</textarea>
                        @error('delivery_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Frais --}}
                    <div class="mb-4">
                        <label for="delivery_fee" class="form-label fw-semibold">Frais de livraison (FCFA)</label>
                        <div class="input-group">
                            <input type="number" name="delivery_fee" id="delivery_fee"
                                   class="form-control @error('delivery_fee') is-invalid @enderror"
                                   min="0" step="100" placeholder="0"
                                   value="{{ old('delivery_fee', $delivery->delivery_fee) }}">
                            <span class="input-group-text">FCFA</span>
                        </div>
                        @error('delivery_fee')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Notes --}}
                    <div class="mb-4">
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="form-control @error('notes') is-invalid @enderror"
                                  placeholder="Instructions spéciales…">{{ old('notes', $delivery->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Info lecture seule --}}
                    <div class="bg-light rounded p-3 mb-4 small text-muted">
                        <i class="bi bi-lock me-1"></i>
                        Le client et la référence ne peuvent pas être modifiés après création.
                        <br>
                        <strong>Client :</strong>
                        {{ $delivery->client?->first_name }} {{ $delivery->client?->last_name }}
                        &nbsp;|&nbsp;
                        <strong>Réf. :</strong> {{ $delivery->reference }}
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <a href="{{ route('deliveries.show', $delivery) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const radios = document.querySelectorAll('input[name="type"]');
    const addressBlock = document.getElementById('address-block');
    function toggleAddress() {
        const val = document.querySelector('input[name="type"]:checked')?.value;
        addressBlock.style.display = val === 'retrait_boutique' ? 'none' : '';
    }
    radios.forEach(r => r.addEventListener('change', toggleAddress));
    toggleAddress();
</script>
@endpush
@endsection
