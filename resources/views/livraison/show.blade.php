@extends('layouts.app')

@section('title', 'Livraison ' . $delivery->reference)

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('deliveries.index') }}">Livraisons</a></li>
            <li class="breadcrumb-item active">{{ $delivery->reference }}</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 fw-bold mb-0">{{ $delivery->reference }}</h1>
            <small class="text-muted">Créée le {{ $delivery->created_at->format('d/m/Y à H:i') }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('deliveries.edit', $delivery) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            <form method="POST" action="{{ route('deliveries.destroy', $delivery) }}"
                  onsubmit="return confirm('Supprimer définitivement cette livraison ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash me-1"></i>Supprimer
                </button>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- Infos principales --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Informations</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <p class="text-muted small mb-1">Client</p>
                        <p class="fw-semibold mb-0">
                            @if($delivery->client)
                                {{ $delivery->client->first_name }} {{ $delivery->client->last_name }}
                                @if($delivery->client->phone)
                                    <br><small class="text-muted fw-normal">{{ $delivery->client->phone }}</small>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-sm-6">
                        <p class="text-muted small mb-1">Type</p>
                        <p class="mb-0">
                            @if($delivery->type === 'livraison')
                                <span class="badge bg-info-subtle text-info border border-info-subtle fs-6 fw-normal px-3 py-2">
                                    <i class="bi bi-truck me-1"></i>Livraison à domicile
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle fs-6 fw-normal px-3 py-2">
                                    <i class="bi bi-shop me-1"></i>Retrait en boutique
                                </span>
                            @endif
                        </p>
                    </div>
                    @if($delivery->delivery_address)
                    <div class="col-12">
                        <p class="text-muted small mb-1">Adresse de livraison</p>
                        <p class="mb-0">{{ $delivery->delivery_address }}</p>
                    </div>
                    @endif
                    <div class="col-sm-6">
                        <p class="text-muted small mb-1">Frais de livraison</p>
                        <p class="fw-semibold mb-0">
                            {{ $delivery->delivery_fee ? number_format($delivery->delivery_fee, 0, ',', ' ') . ' FCFA' : '—' }}
                        </p>
                    </div>
                    @if($delivery->notes)
                    <div class="col-12">
                        <p class="text-muted small mb-1">Notes</p>
                        <p class="mb-0 fst-italic">{{ $delivery->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Preuve de livraison --}}
        @if($delivery->proof_photo)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-image me-2 text-success"></i>Preuve de livraison</h6>
            </div>
            <div class="card-body text-center">
                <img src="{{ Storage::url($delivery->proof_photo) }}" alt="Preuve"
                     class="img-fluid rounded" style="max-height:300px">
                @if($delivery->delivered_at)
                    <p class="text-muted small mt-2 mb-0">
                        Livré le {{ $delivery->delivered_at->format('d/m/Y à H:i') }}
                    </p>
                @endif
            </div>
        </div>
        @endif

        {{-- Upload preuve — POST deliveries.proof --}}
        @if($delivery->status !== 'delivered')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-upload me-2 text-info"></i>Enregistrer la preuve</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('deliveries.proof', $delivery) }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small">Photo de preuve</label>
                        <input type="file" name="proof_photo" accept="image/*"
                               class="form-control form-control-sm" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-check-lg me-1"></i>Confirmer la livraison
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Panneau latéral --}}
    <div class="col-lg-4">

        {{-- Changer statut — PUT deliveries.status --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-arrow-repeat me-2 text-info"></i>Statut</h6>
            </div>
            <div class="card-body">
                @php
                    $statusConfig = [
                        'pending'    => ['label'=>'En attente',  'class'=>'secondary', 'icon'=>'clock'],
                        'assigned'   => ['label'=>'Assignée',    'class'=>'primary',   'icon'=>'person-check'],
                        'in_transit' => ['label'=>'En transit',  'class'=>'info',      'icon'=>'truck'],
                        'delivered'  => ['label'=>'Livrée',      'class'=>'success',   'icon'=>'check-circle'],
                        'failed'     => ['label'=>'Échouée',     'class'=>'danger',    'icon'=>'x-circle'],
                        'returned'   => ['label'=>'Retournée',   'class'=>'warning',   'icon'=>'arrow-return-left'],
                    ];
                    $cfg = $statusConfig[$delivery->status] ?? ['label'=>$delivery->status,'class'=>'secondary','icon'=>'circle'];
                @endphp
                <div class="text-center mb-3">
                    <span class="badge bg-{{ $cfg['class'] }}-subtle text-{{ $cfg['class'] }} border border-{{ $cfg['class'] }}-subtle fs-6 px-3 py-2">
                        <i class="bi bi-{{ $cfg['icon'] }} me-2"></i>{{ $cfg['label'] }}
                    </span>
                </div>
                <form method="POST" action="{{ route('deliveries.status', $delivery) }}">
                    @csrf @method('PUT')
                    <select name="status" class="form-select form-select-sm mb-2">
                        @foreach(['pending'=>'En attente','assigned'=>'Assignée','in_transit'=>'En transit','delivered'=>'Livrée','failed'=>'Échouée','returned'=>'Retournée'] as $val => $label)
                            <option value="{{ $val }}" @selected($delivery->status === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-info w-100">
                        Mettre à jour le statut
                    </button>
                </form>
            </div>
        </div>

        {{-- Assigner livreur — PUT deliveries.assign --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-person-badge me-2 text-primary"></i>Livreur</h6>
            </div>
            <div class="card-body">
                @if($delivery->driver)
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                             style="width:44px;height:44px;font-size:1.1rem">
                            {{ strtoupper(substr($delivery->driver->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="fw-semibold mb-0">{{ $delivery->driver->name }}</p>
                            @if($delivery->driver->phone)
                                <small class="text-muted">{{ $delivery->driver->phone }}</small>
                            @endif
                        </div>
                    </div>
                    @if($delivery->assigned_at)
                        <small class="text-muted d-block mb-3">Assigné le {{ $delivery->assigned_at->format('d/m/Y à H:i') }}</small>
                    @endif
                    {{-- Réassigner --}}
                    <form method="POST" action="{{ route('deliveries.assign', $delivery) }}">
                        @csrf @method('PUT')
                        <select name="driver_id" class="form-select form-select-sm mb-2" required>
                            <option value="">Réassigner à…</option>
                            @foreach(App\Models\User::where('role','delivery')->where('is_active',true)->get() as $d)
                                <option value="{{ $d->id }}" @selected($delivery->driver_id === $d->id)>{{ $d->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">Réassigner</button>
                    </form>
                @else
                    <p class="text-muted small text-center fst-italic mb-3">Aucun livreur assigné</p>
                    <form method="POST" action="{{ route('deliveries.assign', $delivery) }}">
                        @csrf @method('PUT')
                        <select name="driver_id" class="form-select form-select-sm mb-2" required>
                            <option value="">Choisir un livreur…</option>
                            @foreach(App\Models\User::where('role','delivery')->where('is_active',true)->get() as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary w-100">Assigner</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Chronologie --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-calendar3 me-2 text-secondary"></i>Chronologie</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Créée</span>
                        <span>{{ $delivery->created_at->format('d/m/Y H:i') }}</span>
                    </li>
                    @if($delivery->assigned_at)
                    <li class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Assignée</span>
                        <span>{{ $delivery->assigned_at->format('d/m/Y H:i') }}</span>
                    </li>
                    @endif
                    @if($delivery->delivered_at)
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Livrée</span>
                        <span class="text-success fw-semibold">{{ $delivery->delivered_at->format('d/m/Y H:i') }}</span>
                    </li>
                    @endif
                </ul>
            </div>
        </div>

    </div>
</div>
@endsection
