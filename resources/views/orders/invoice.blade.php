<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $order->reference }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f0f0;
            color: #1a1a2e;
            font-size: 13px;
            line-height: 1.5;
        }

        .page {
            background: #fff;
            max-width: 800px;
            margin: 30px auto;
            padding: 48px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.10);
            border-radius: 4px;
        }

        /* ── Header ────────────────────────────── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 32px;
            border-bottom: 2px solid #f0ede8;
        }

        .brand-logo {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #f0ede8;
            margin-bottom: 10px;
        }

        .brand-logo img { width: 100%; height: 100%; object-fit: contain; }

        .brand-name { display: none; }

        .brand-sub {
            font-size: 12px;
            color: #E8820C;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 10px;
        }

        .brand-contact {
            font-size: 11px;
            color: #888;
            margin-top: 6px;
            line-height: 1.8;
        }

        .invoice-meta { text-align: right; }

        .invoice-title {
            font-size: 28px;
            font-weight: 900;
            color: #E8820C;
            letter-spacing: -1px;
            text-transform: uppercase;
        }

        .invoice-ref {
            font-size: 13px;
            font-weight: 700;
            color: #1a1a2e;
            margin-top: 4px;
            font-family: 'Courier New', monospace;
        }

        .invoice-dates {
            font-size: 11px;
            color: #888;
            margin-top: 8px;
            line-height: 1.8;
        }

        /* ── Parties ───────────────────────────── */
        .parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-bottom: 36px;
        }

        .party-card {
            background: #fafaf8;
            border: 1px solid #f0ede8;
            border-radius: 8px;
            padding: 18px 20px;
        }

        .party-label {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #E8820C;
            margin-bottom: 10px;
        }

        .party-name {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 4px;
        }

        .party-info {
            font-size: 11px;
            color: #666;
            line-height: 1.8;
        }

        /* ── Tableau articles ──────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }

        .items-table thead tr {
            background: #1a1a2e;
            color: #fff;
        }

        .items-table thead th {
            padding: 10px 14px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .items-table thead th:last-child,
        .items-table thead th:nth-child(3),
        .items-table thead th:nth-child(4) { text-align: right; }

        .items-table tbody tr {
            border-bottom: 1px solid #f0ede8;
            transition: background 0.1s;
        }

        .items-table tbody tr:last-child { border-bottom: none; }

        .items-table tbody tr:nth-child(even) { background: #fafaf8; }

        .items-table tbody td {
            padding: 12px 14px;
            font-size: 12px;
            color: #1a1a2e;
            vertical-align: middle;
        }

        .items-table tbody td:nth-child(3),
        .items-table tbody td:nth-child(4),
        .items-table tbody td:last-child { text-align: right; }

        .product-name { font-weight: 600; }

        .product-ref { font-size: 10px; color: #aaa; font-family: 'Courier New', monospace; }

        /* ── Totaux ────────────────────────────── */
        .totals-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 32px;
        }

        .totals-box {
            width: 300px;
            border: 1px solid #f0ede8;
            border-radius: 8px;
            overflow: hidden;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 16px;
            font-size: 12px;
            border-bottom: 1px solid #f0ede8;
        }

        .totals-row:last-child { border-bottom: none; }

        .totals-row .label { color: #666; }
        .totals-row .value { font-weight: 600; color: #1a1a2e; }

        .totals-row.total-line {
            background: #1a1a2e;
            padding: 13px 16px;
        }

        .totals-row.total-line .label {
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .totals-row.total-line .value {
            color: #E8820C;
            font-size: 16px;
            font-weight: 800;
        }

        /* ── Paiement ──────────────────────────── */
        .payment-section {
            display: flex;
            gap: 20px;
            margin-bottom: 36px;
        }

        .payment-card {
            flex: 1;
            border-radius: 8px;
            padding: 16px 20px;
            border: 1px solid;
        }

        .payment-card.paid   { background: #f0fdf4; border-color: #bbf7d0; }
        .payment-card.partial { background: #fffbeb; border-color: #fde68a; }
        .payment-card.unpaid  { background: #fef2f2; border-color: #fecaca; }

        .payment-card-label {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 6px;
        }

        .payment-card.paid   .payment-card-label { color: #16a34a; }
        .payment-card.partial .payment-card-label { color: #d97706; }
        .payment-card.unpaid  .payment-card-label { color: #dc2626; }

        .payment-card-value {
            font-size: 18px;
            font-weight: 800;
        }

        .payment-card.paid   .payment-card-value { color: #16a34a; }
        .payment-card.partial .payment-card-value { color: #d97706; }
        .payment-card.unpaid  .payment-card-value { color: #dc2626; }

        .payment-card-sub { font-size: 10px; color: #888; margin-top: 2px; }

        /* ── Notes ─────────────────────────────── */
        .notes-section {
            background: #fafaf8;
            border: 1px solid #f0ede8;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 32px;
        }

        .notes-label {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #888;
            margin-bottom: 6px;
        }

        .notes-text { font-size: 12px; color: #555; }

        /* ── Footer ────────────────────────────── */
        .footer {
            border-top: 1px solid #f0ede8;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-brand { font-size: 10px; color: #aaa; }
        .footer-brand strong { color: #1a1a2e; }

        .footer-thanks {
            font-size: 11px;
            color: #888;
            font-style: italic;
        }

        /* ── Status badge ──────────────────────── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Print button ──────────────────────── */
        .print-bar {
            max-width: 800px;
            margin: 0 auto 0;
            padding: 12px 16px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }

        .btn-print { background: #E8820C; color: #fff; }
        .btn-back  { background: #fff; color: #555; border: 1px solid #ddd; }

        .btn:hover { opacity: 0.88; }

        /* ── Cachet + Signature ────────────────── */
        .stamp-section {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 32px;
            padding: 24px 0;
            border-top: 1px dashed #e0ddd8;
        }

        .stamp-left, .stamp-right { flex: 1; }

        .stamp-label {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #aaa;
            margin-bottom: 10px;
            text-align: center;
        }

        .stamp-box {
            border: 1.5px dashed #ddd;
            border-radius: 8px;
            height: 80px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 8px;
        }

        .stamp-box-hint {
            font-size: 9px;
            color: #ccc;
            font-style: italic;
        }

        .stamp-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
        }

        /* Cachet circulaire */
        .official-stamp { text-align: center; }

        .stamp-ring {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 3px solid #E8820C;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 0 0 1px #E8820C40, inset 0 0 0 2px #E8820C20;
        }

        .stamp-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1px;
            padding: 8px;
        }

        .stamp-logo {
            width: 28px;
            height: 28px;
            object-fit: contain;
            border-radius: 4px;
        }

        .stamp-company {
            font-size: 13px;
            font-weight: 900;
            color: #1a1a2e;
            letter-spacing: 1px;
            line-height: 1;
        }

        .stamp-domain {
            font-size: 8px;
            color: #E8820C;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .stamp-divider {
            width: 40px;
            height: 1px;
            background: #E8820C;
            margin: 2px 0;
        }

        .stamp-slogan {
            font-size: 6.5px;
            color: #666;
            font-weight: 600;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.3;
        }

        .stamp-caption {
            font-size: 8px;
            color: #aaa;
            margin-top: 6px;
            font-style: italic;
        }

        /* ── Vignette PAYÉ ─────────────────────── */
        .paid-vignette {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 10px;
            padding: 5px 14px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            background: #dcfce7;
            color: #15803d;
            border: 2px solid #16a34a;
        }

        .paid-vignette.partial {
            background: #fef3c7;
            color: #b45309;
            border-color: #d97706;
        }

        .paid-vignette.unpaid {
            background: #fee2e2;
            color: #b91c1c;
            border-color: #dc2626;
        }

        .invoice-meta-wrapper { text-align: right; }

        /* ── Print styles ──────────────────────── */
        @media print {
            body { background: #fff; }
            .page { margin: 0; padding: 32px; box-shadow: none; border-radius: 0; }
            .print-bar { display: none; }
        }
    </style>
</head>
<body>

    {{-- ── Barre actions (non imprimée) ──────────── --}}
    <div class="print-bar">
        <a href="{{ route('orders.index') }}" class="btn btn-back">
            ← Retour
        </a>
        <button onclick="window.print()" class="btn btn-print">
            🖨 Imprimer / PDF
        </button>
    </div>

    <div class="page">

        {{-- ── En-tête ──────────────────────────── --}}
        <div class="header">
            <div>
                <div class="brand-logo">
                    <img src="{{ asset('images/logo-gsit.jpg') }}" alt="{{ config('app.name') }}">
                </div>
                <div class="brand-sub" style="margin-top:10px;">Confection Haute Couture</div>
                <div class="brand-contact">
                    admin@gsit.art<br>
                    gsit.art<br>
                    Brazzaville, Congo
                </div>
            </div>

            <div class="invoice-meta-wrapper">
                {{-- Vignette statut paiement --}}
                @php
                    $vignetteClass = match($order->payment_status) {
                        'paid'    => '',
                        'partial' => 'partial',
                        default   => 'unpaid',
                    };
                    $vignetteText = match($order->payment_status) {
                        'paid'    => 'PAYÉ',
                        'partial' => 'PARTIEL',
                        default   => 'IMPAYÉ',
                    };
                @endphp
                <div class="paid-vignette {{ $vignetteClass }}">{{ $vignetteText }}</div>

                <div class="invoice-meta">
                <div class="invoice-title">Facture</div>
                @php
                    $invoiceNum = 'CG-BZV-' . $order->created_at->format('Ymd') . '-GSIT';
                @endphp
                <div class="invoice-ref">{{ $invoiceNum }}</div>
                <div style="font-size:10px; color:#aaa; font-family:'Courier New',monospace; margin-top:2px;">
                    Réf interne : {{ $order->reference }}
                </div>
                <div class="invoice-dates">
                    Émise le : {{ $order->created_at->format('d/m/Y à H:i') }}<br>
                    @if($order->confirmed_at)
                        Confirmée le : {{ $order->confirmed_at->format('d/m/Y') }}<br>
                    @endif
                    @if($order->delivered_at)
                        Livrée le : {{ $order->delivered_at->format('d/m/Y') }}<br>
                    @endif
                    Caissier : {{ $order->cashier?->name ?? '—' }}<br>
                    Agence : {{ env('AGENCE_NOM', 'Agence Plateau') }}
                </div>
                <div style="margin-top:10px;">
                    @php
                        $statusColors = [
                            'pending'    => '#f59e0b',
                            'confirmed'  => '#3b82f6',
                            'processing' => '#6366f1',
                            'ready'      => '#10b981',
                            'delivered'  => '#059669',
                            'cancelled'  => '#ef4444',
                        ];
                        $statusBgs = [
                            'pending'    => '#fffbeb',
                            'confirmed'  => '#eff6ff',
                            'processing' => '#eef2ff',
                            'ready'      => '#f0fdf4',
                            'delivered'  => '#ecfdf5',
                            'cancelled'  => '#fef2f2',
                        ];
                    @endphp
                    <span class="status-badge"
                          style="background:{{ $statusBgs[$order->status] ?? '#f9fafb' }}; color:{{ $statusColors[$order->status] ?? '#6b7280' }}">
                        {{ $order->getStatusLabel() }}
                    </span>
                </div>
            </div>{{-- end invoice-meta --}}
            </div>{{-- end invoice-meta-wrapper --}}
        </div>

        {{-- ── Parties ───────────────────────────── --}}
        <div class="parties">
            <div class="party-card">
                <div class="party-label">Vendeur</div>
                <div class="party-name" style="font-size:13px; color:#888; font-weight:500;">Confection Haute Couture</div>
                <div class="party-info">
                    admin@gsit.art<br>
                    {{ env('AGENCE_NOM', 'Agence Plateau') }}, Brazzaville, Congo
                </div>
            </div>

            <div class="party-card">
                <div class="party-label">Client</div>
                <div class="party-name">{{ $order->client?->full_name ?? 'Client supprimé' }}</div>
                <div class="party-info">
                    @if($order->client?->phone){{ $order->client->phone }}<br>@endif
                    @if($order->client?->email){{ $order->client->email }}<br>@endif
                    @if($order->client?->city){{ $order->client->city }}@endif
                </div>
            </div>
        </div>

        {{-- ── Tableau articles ──────────────────── --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:36px">#</th>
                    <th>Article</th>
                    <th>Type</th>
                    <th>Qté</th>
                    <th>P.U.</th>
                    <th>Remise</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $i => $item)
                    <tr>
                        <td style="color:#aaa; font-size:11px; text-align:center;">{{ $i + 1 }}</td>
                        <td>
                            <div class="product-name">{{ $item->product_name }}</div>
                            @if($item->product)
                                <div class="product-ref">{{ $item->product->reference }}</div>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:10px; background:#f0ede8; color:#666; padding:2px 8px; border-radius:99px; white-space:nowrap;">
                                {{ $item->unit === 'm' ? 'Tissu' : 'PAP' }}
                            </span>
                        </td>
                        <td style="text-align:right; font-weight:600;">
                            {{ number_format($item->quantity, $item->unit === 'm' ? 2 : 0, ',', ' ') }}
                            <span style="color:#aaa; font-size:10px;">{{ $item->unit }}</span>
                        </td>
                        <td style="text-align:right;">
                            {{ number_format($item->unit_price, 0, ',', ' ') }}
                            <span style="color:#aaa; font-size:10px;">{{ env('CURRENCY','FCFA') }}</span>
                        </td>
                        <td style="text-align:right; color:#aaa;">
                            {{ $item->discount > 0 ? '- ' . number_format($item->discount, 0, ',', ' ') : '—' }}
                        </td>
                        <td style="text-align:right; font-weight:700; color:#1a1a2e;">
                            {{ number_format($item->total, 0, ',', ' ') }}
                            <span style="color:#aaa; font-size:10px; font-weight:400;">{{ env('CURRENCY','FCFA') }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ── Totaux ────────────────────────────── --}}
        <div class="totals-wrapper">
            <div class="totals-box">
                <div class="totals-row">
                    <span class="label">Sous-total</span>
                    <span class="value">{{ number_format($order->subtotal, 0, ',', ' ') }} {{ env('CURRENCY','FCFA') }}</span>
                </div>
                @if($order->discount > 0)
                    <div class="totals-row">
                        <span class="label">Remise</span>
                        <span class="value" style="color:#16a34a;">- {{ number_format($order->discount, 0, ',', ' ') }} {{ env('CURRENCY','FCFA') }}</span>
                    </div>
                @endif
                <div class="totals-row total-line">
                    <span class="label">Total TTC</span>
                    <span class="value">{{ number_format($order->total, 0, ',', ' ') }} {{ env('CURRENCY','FCFA') }}</span>
                </div>
            </div>
        </div>

        {{-- ── Paiement ──────────────────────────── --}}
        @php
            $balance = $order->total - $order->amount_paid;
            $payClass = $order->payment_status === 'paid' ? 'paid' : ($order->payment_status === 'partial' ? 'partial' : 'unpaid');
            $payLabel = match($order->payment_status) {
                'paid'    => 'Payé intégralement',
                'partial' => 'Paiement partiel',
                default   => 'Non payé',
            };
            $payMethod = match($order->payment_method) {
                'cash'         => 'Espèces',
                'mobile_money' => 'Mobile Money',
                'card'         => 'Carte bancaire',
                'credit'       => 'Crédit',
                default        => $order->payment_method ?? '—',
            };
        @endphp

        <div class="payment-section">
            <div class="payment-card {{ $payClass }}">
                <div class="payment-card-label">Statut paiement</div>
                <div class="payment-card-value">{{ $payLabel }}</div>
                <div class="payment-card-sub">Mode : {{ $payMethod }}</div>
            </div>

            <div class="payment-card paid">
                <div class="payment-card-label">Montant encaissé</div>
                <div class="payment-card-value">{{ number_format($order->amount_paid, 0, ',', ' ') }}</div>
                <div class="payment-card-sub">{{ env('CURRENCY','FCFA') }}</div>
            </div>

            @if($balance > 0)
                <div class="payment-card unpaid">
                    <div class="payment-card-label">Reste à payer</div>
                    <div class="payment-card-value">{{ number_format($balance, 0, ',', ' ') }}</div>
                    <div class="payment-card-sub">{{ env('CURRENCY','FCFA') }}</div>
                </div>
            @endif
        </div>

        {{-- ── Notes ─────────────────────────────── --}}
        @if($order->notes)
            <div class="notes-section">
                <div class="notes-label">Notes</div>
                <div class="notes-text">{{ $order->notes }}</div>
            </div>
        @endif

        {{-- ── Cachet + Signature ─────────────────── --}}
        <div class="stamp-section">
            <div class="stamp-left">
                <p class="stamp-label">Signature du client</p>
                <div class="stamp-box">
                    <p class="stamp-box-hint">Signature &amp; date</p>
                </div>
            </div>

            <div class="stamp-center">
                {{-- Cachet officiel GSIT --}}
                <div class="official-stamp">
                    <div class="stamp-ring">
                        <div class="stamp-inner">
                            <img src="{{ asset('images/logo-gsit.jpg') }}" alt="GSIT" class="stamp-logo">
                            <div class="stamp-company">GSIT</div>
                            <div class="stamp-domain">gsit.art</div>
                            <div class="stamp-divider"></div>
                            <div class="stamp-slogan">Confection Haute Couture</div>
                        </div>
                    </div>
                    <p class="stamp-caption">Cachet officiel</p>
                </div>
            </div>

            <div class="stamp-right">
                <p class="stamp-label">Responsable GSIT</p>
                <div class="stamp-box">
                    <p class="stamp-box-hint">Signature &amp; cachet</p>
                </div>
            </div>
        </div>

        {{-- ── Footer ────────────────────────────── --}}
        <div class="footer">
            <div class="footer-brand">
                Généré par <strong>GSIT</strong> — <span style="color:#E8820C">gsit.art</span><br>
                le {{ now()->format('d/m/Y à H:i') }} • {{ env('AGENCE_NOM', 'Agence Plateau') }}
            </div>
            <div class="footer-thanks">
                Merci pour votre confiance 🙏
            </div>
        </div>

    </div>

</body>
</html>
