<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devis {{ $quote->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; }
        .page { padding: 40px; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; padding-bottom: 20px; border-bottom: 2px solid #4F46E5; }
        .company { }
        .company h1 { font-size: 22px; font-weight: 700; color: #4F46E5; letter-spacing: 1px; }
        .company p { font-size: 11px; color: #6B7280; margin-top: 2px; }

        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 11px; }
        .badge-brouillon { background: #F3F4F6; color: #6B7280; }
        .badge-envoye    { background: #EFF6FF; color: #2563EB; }
        .badge-accepte   { background: #ECFDF5; color: #059669; }
        .badge-refuse    { background: #FEF2F2; color: #DC2626; }
        .badge-expire    { background: #FFF7ED; color: #D97706; }

        .devis-info { margin-bottom: 24px; }
        .devis-info h2 { font-size: 18px; font-weight: 700; color: #1a1a2e; }
        .devis-info .ref { font-family: monospace; color: #4F46E5; font-size: 14px; }

        .grid-2 { display: flex; gap: 20px; margin-bottom: 20px; }
        .grid-2 > div { flex: 1; }

        .card { background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 8px; padding: 14px; margin-bottom: 16px; }
        .card h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #9CA3AF; margin-bottom: 8px; font-weight: 700; }
        .card .field { margin-bottom: 6px; }
        .card .label { font-size: 10px; color: #6B7280; }
        .card .value { font-size: 12px; font-weight: 600; color: #1a1a2e; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.items th { background: #4F46E5; color: #fff; padding: 8px 10px; font-size: 10px; text-align: left; font-weight: 700; }
        table.items td { padding: 8px 10px; font-size: 11px; border-bottom: 1px solid #E5E7EB; }
        table.items tr:nth-child(even) td { background: #F9FAFB; }

        .total-box { background: #4F46E5; color: #fff; padding: 14px 16px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .total-box .label { font-size: 13px; font-weight: 600; }
        .total-box .amount { font-size: 22px; font-weight: 700; }

        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #E5E7EB; display: flex; justify-content: space-between; font-size: 10px; color: #9CA3AF; }

        .validity { background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; font-size: 11px; color: #92400E; }
        .notes-box { background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; font-size: 11px; color: #1E40AF; }
        .signature { margin-top: 32px; }
        .sig-box { display: inline-block; border: 1px dashed #D1D5DB; border-radius: 6px; padding: 12px 24px; min-width: 200px; min-height: 60px; font-size: 10px; color: #9CA3AF; }
    </style>
</head>
<body>
<div class="page">

    {{-- En-tête --}}
    <div class="header">
        <div class="company">
            <h1>GSIT</h1>
            <p>Gestion Interne — Atelier couture</p>
        </div>
        <div style="text-align:right">
            <div style="font-size:16px; font-weight:700; color:#4F46E5; margin-bottom:4px">DEVIS</div>
            <div style="font-family:monospace; font-size:13px; color:#374151;">{{ $quote->reference }}</div>
            <div style="margin-top:6px">
                <span class="badge badge-{{ $quote->status }}">{{ $quote->getStatusLabel() }}</span>
            </div>
        </div>
    </div>

    {{-- Client + info devis --}}
    <div class="grid-2">
        <div class="card">
            <h3>Client</h3>
            <div class="field">
                <div class="value" style="font-size:14px">{{ $quote->client->full_name }}</div>
            </div>
            <div class="field">
                <div class="label">Téléphone</div>
                <div class="value">{{ $quote->client->phone }}</div>
            </div>
            @if($quote->client->email)
            <div class="field">
                <div class="label">Email</div>
                <div class="value">{{ $quote->client->email }}</div>
            </div>
            @endif
        </div>
        <div class="card">
            <h3>Informations devis</h3>
            <div class="field">
                <div class="label">Date d'émission</div>
                <div class="value">{{ $quote->created_at->format('d/m/Y') }}</div>
            </div>
            @if($quote->valid_until)
            <div class="field">
                <div class="label">Valide jusqu'au</div>
                <div class="value" style="color:{{ $quote->valid_until->isPast() ? '#DC2626' : '#059669' }}">{{ $quote->valid_until->format('d/m/Y') }}</div>
            </div>
            @endif
            @if($quote->delivery_date)
            <div class="field">
                <div class="label">Délai de livraison estimé</div>
                <div class="value">{{ $quote->delivery_date->format('d/m/Y') }}</div>
            </div>
            @endif
            <div class="field">
                <div class="label">Établi par</div>
                <div class="value">{{ $quote->creator->name }}</div>
            </div>
        </div>
    </div>

    {{-- Objet --}}
    @if($quote->garment_type || $quote->model_name)
    <div class="card">
        <h3>Objet du devis</h3>
        <div style="font-size:13px; font-weight:600; color:#1a1a2e; margin-bottom:4px">
            {{ ucfirst($quote->garment_type ?? '') }} {{ $quote->model_name ? '— ' . $quote->model_name : '' }}
            @if($quote->gender) ({{ ucfirst($quote->gender) }}) @endif
        </div>
        @if($quote->model_description)
        <div style="font-size:11px; color:#6B7280; margin-top:4px">{{ $quote->model_description }}</div>
        @endif
    </div>
    @endif

    {{-- Tableau des prestations --}}
    <table class="items">
        <thead>
            <tr>
                <th>Désignation</th>
                <th style="text-align:right">Quantité</th>
                <th style="text-align:right">Prix unitaire</th>
                <th style="text-align:right">Total</th>
            </tr>
        </thead>
        <tbody>
            @if($quote->fabric_cost > 0)
            <tr>
                <td>
                    Tissu{{ $quote->fabric ? ' — ' . $quote->fabric->name : ($quote->fabric_name ? ' — ' . $quote->fabric_name : '') }}
                    @if($quote->fabric_color) ({{ $quote->fabric_color }}) @endif
                </td>
                <td style="text-align:right">{{ $quote->fabric_meters ?? '—' }} m</td>
                <td style="text-align:right">{{ number_format($quote->fabric ? $quote->fabric->price_per_meter : ($quote->fabric_price_per_meter ?? 0), 0, ',', ' ') }} FCFA/m</td>
                <td style="text-align:right; font-weight:600">{{ number_format($quote->fabric_cost, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            <tr>
                <td>Confection {{ $quote->garment_type ?? '' }}</td>
                <td style="text-align:right">1</td>
                <td style="text-align:right">{{ number_format($quote->labor_cost, 0, ',', ' ') }} FCFA</td>
                <td style="text-align:right; font-weight:600">{{ number_format($quote->labor_cost, 0, ',', ' ') }} FCFA</td>
            </tr>
            @foreach(collect($quote->garments ?? [])->flatMap(fn($g) => $g['garment_type_entries'] ?? [])->filter(fn($e) => ($e['price'] ?? 0) > 0) as $typeEntry)
            <tr>
                <td>Type de vêtement — {{ $typeEntry['value'] }}</td>
                <td style="text-align:right">{{ $typeEntry['qty'] ?? 1 }}</td>
                <td style="text-align:right">{{ number_format($typeEntry['price'], 0, ',', ' ') }} FCFA</td>
                <td style="text-align:right; font-weight:600">{{ number_format($typeEntry['line_total'] ?? $typeEntry['price'], 0, ',', ' ') }} FCFA</td>
            </tr>
            @endforeach
            @if($quote->accessories && count($quote->accessories))
                @foreach($quote->accessories as $acc)
                <tr>
                    <td>{{ $acc['name'] ?? 'Accessoire' }}</td>
                    <td style="text-align:right">{{ $acc['qty'] ?? 1 }}</td>
                    <td style="text-align:right">{{ number_format($acc['price'] ?? 0, 0, ',', ' ') }} FCFA</td>
                    <td style="text-align:right; font-weight:600">{{ number_format(($acc['price'] ?? 0) * ($acc['qty'] ?? 1), 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    {{-- Total --}}
    <div class="total-box">
        <div class="label">MONTANT TOTAL ESTIMÉ (TTC)</div>
        <div class="amount">{{ number_format($quote->total, 0, ',', ' ') }} FCFA</div>
    </div>

    {{-- Validité --}}
    @if($quote->valid_until)
    <div class="validity">
        ⏱ Ce devis est valable jusqu'au {{ $quote->valid_until->format('d/m/Y') }}.
        Au-delà de cette date, les prix pourront être révisés.
    </div>
    @endif

    {{-- Notes --}}
    @if($quote->notes)
    <div class="notes-box">
        <strong>Conditions particulières :</strong> {{ $quote->notes }}
    </div>
    @endif

    {{-- Signatures --}}
    <div class="signature" style="display:flex; justify-content:space-between; margin-top:40px">
        <div>
            <div class="sig-box">
                <div style="margin-bottom:40px">Signature de l'atelier :</div>
            </div>
            <p style="font-size:10px; color:#9CA3AF; margin-top:4px">{{ $quote->creator->name }}</p>
        </div>
        <div>
            <div class="sig-box">
                <div style="margin-bottom:40px">Bon pour accord (client) :</div>
            </div>
            <p style="font-size:10px; color:#9CA3AF; margin-top:4px">{{ $quote->client->full_name }}</p>
        </div>
    </div>

    {{-- Pied de page --}}
    <div class="footer">
        <span>GSIT — Gestion Interne Atelier</span>
        <span>Devis {{ $quote->reference }} — {{ $quote->created_at->format('d/m/Y') }}</span>
    </div>
</div>
</body>
</html>
