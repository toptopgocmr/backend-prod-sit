<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:9px; color:#1A1A2E; background:#fff; }

    .header { padding:16px 24px 12px; border-bottom:3px solid #7C3AED; display:flex; justify-content:space-between; align-items:flex-start; }
    .logo-block .company { font-size:18px; font-weight:700; color:#1A1A2E; }
    .logo-block .subtitle { font-size:8px; color:#7C3AED; font-weight:600; letter-spacing:2px; text-transform:uppercase; }
    .meta { text-align:right; }
    .meta .title { font-size:14px; font-weight:700; color:#7C3AED; }
    .meta .date { font-size:8px; color:#888; margin-top:3px; }

    .stats { display:flex; gap:0; border-bottom:1px solid #eee; }
    .stat { flex:1; padding:8px 14px; border-right:1px solid #eee; }
    .stat:last-child { border-right:none; }
    .stat .label { font-size:7px; color:#888; text-transform:uppercase; letter-spacing:1px; }
    .stat .value { font-size:13px; font-weight:700; color:#1A1A2E; margin-top:2px; }
    .stat .value.purple { color:#7C3AED; }
    .stat .value.green  { color:#16a34a; }
    .stat .value.orange { color:#ea580c; }

    table { width:100%; border-collapse:collapse; }
    thead tr { background:#1A1A2E; color:#fff; }
    thead th { padding:6px 8px; text-align:left; font-size:7px; text-transform:uppercase; letter-spacing:0.8px; font-weight:600; }
    thead th.right { text-align:right; }
    thead th.center { text-align:center; }
    tbody tr { border-bottom:1px solid #f0f0f0; }
    tbody tr:nth-child(even) { background:#fafafa; }
    td { padding:5px 8px; font-size:8px; vertical-align:middle; }
    td.right { text-align:right; }
    td.center { text-align:center; }
    td.mono { font-family:DejaVu Sans Mono,monospace; font-size:7px; color:#7C3AED; font-weight:700; }

    .badge { display:inline-block; padding:1px 6px; border-radius:8px; font-size:7px; font-weight:600; }
    .badge-paid    { background:#dcfce7; color:#15803d; }
    .badge-partial { background:#fef9c3; color:#a16207; }
    .badge-unpaid  { background:#fee2e2; color:#b91c1c; }

    .progress-bar { height:4px; background:#E5E7EB; border-radius:2px; min-width:50px; }
    .progress-fill { height:4px; background:#7C3AED; border-radius:2px; }

    .footer { margin-top:12px; padding:8px 24px; border-top:1px solid #eee; display:flex; justify-content:space-between; font-size:7px; color:#aaa; }
</style>
</head>
<body>

<div class="header">
    <div class="logo-block">
        <img src="{{ public_path('images/logo-gsit.jpg') }}" style="width:36px;height:36px;border-radius:6px;object-fit:contain;vertical-align:middle;margin-right:8px;">
        <span style="vertical-align:middle;">
            <div class="company">GSIT</div>
            <div class="subtitle">Haute Couture</div>
        </span>
    </div>
    <div class="meta">
        <div class="title">Commandes Sur Mesure</div>
        <div class="date">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
        @if(request()->hasAny(['search','status','couturier']))
        <div class="date" style="color:#7C3AED;margin-top:2px;">Filtres actifs</div>
        @endif
    </div>
</div>

<div class="stats">
    <div class="stat">
        <div class="label">Total commandes</div>
        <div class="value">{{ $orders->count() }}</div>
    </div>
    <div class="stat">
        <div class="label">Chiffre d'affaires</div>
        <div class="value purple">{{ number_format($orders->sum('total'), 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="stat">
        <div class="label">Encaissé</div>
        <div class="value green">{{ number_format($orders->sum('amount_paid'), 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="stat">
        <div class="label">Reste à percevoir</div>
        <div class="value orange">{{ number_format($orders->sum('balance'), 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="stat">
        <div class="label">En production</div>
        <div class="value">{{ $orders->whereIn('status', ['en_decoupe','en_couture','finition','controle_qualite'])->count() }}</div>
    </div>
    <div class="stat">
        <div class="label">Livrées</div>
        <div class="value green">{{ $orders->where('status','livre')->count() }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Référence</th>
            <th>Date</th>
            <th>Client</th>
            <th>Vêtement</th>
            <th>Couturier</th>
            <th class="center">Prog.</th>
            <th class="right">Total</th>
            <th class="right">Reste</th>
            <th class="center">Paiement</th>
            <th class="center">Statut</th>
            <th class="center">Livraison</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $order)
        <tr>
            <td class="mono">{{ $order->reference }}</td>
            <td>{{ $order->created_at->format('d/m/Y') }}</td>
            <td>
                <div style="font-weight:600;">{{ $order->client->full_name }}</div>
                <div style="color:#888;font-size:7px;">{{ $order->client->phone }}</div>
            </td>
            <td>
                <div>{{ ucfirst($order->garment_type) }}</div>
                <div style="color:#888;font-size:7px;">{{ ucfirst($order->gender) }}</div>
            </td>
            <td>{{ $order->couturier?->name ?? '—' }}</td>
            <td class="center">
                @if($order->status !== 'annule')
                <div class="progress-bar"><div class="progress-fill" style="width:{{ $order->progress_percent }}%"></div></div>
                <div style="font-size:7px;color:#888;margin-top:1px;">{{ $order->progress_percent }}%</div>
                @else
                <span style="color:#f87171;">Annulé</span>
                @endif
            </td>
            <td class="right" style="font-weight:700;">{{ number_format($order->total, 0, ',', ' ') }}</td>
            <td class="right" style="color:{{ $order->balance > 0 ? '#ea580c' : '#16a34a' }};font-weight:{{ $order->balance > 0 ? '700' : '400' }};">
                {{ $order->balance > 0 ? number_format($order->balance, 0, ',', ' ') : '—' }}
            </td>
            <td class="center">
                <span class="badge {{ match($order->payment_status) { 'paid'=>'badge-paid','partial'=>'badge-partial',default=>'badge-unpaid' } }}">
                    {{ match($order->payment_status) { 'paid'=>'Soldé','partial'=>'Partiel',default=>'Impayé' } }}
                </span>
            </td>
            <td class="center" style="font-size:7px;font-weight:600;color:#5B21B6;">{{ $order->getStatusLabel() }}</td>
            <td class="center">
                @if($order->delivery_date)
                <span style="color:{{ $order->delivery_date->isPast() && !in_array($order->status,['livre','annule']) ? '#dc2626' : '#374151' }};">
                    {{ $order->delivery_date->format('d/m/Y') }}
                </span>
                @else
                <span style="color:#d1d5db;">—</span>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="11" style="text-align:center;padding:20px;color:#aaa;">Aucune commande trouvée</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    <span>GSIT Haute Couture &copy; {{ date('Y') }}</span>
    <span>{{ $orders->count() }} commande(s) — Total : {{ number_format($orders->sum('total'), 0, ',', ' ') }} FCFA</span>
</div>

</body>
</html>
