{{--
  Partial : boutons de partage reçu
  Usage : @include('partials.share-receipt', ['order' => $order, 'type' => 'order'])
         @include('partials.share-receipt', ['order' => $customOrder, 'type' => 'custom'])
--}}
@php
    $client = $order->client;
    $phone  = preg_replace('/\D/', '', $client->phone ?? '');
    $ref    = $order->reference;
    $total  = number_format($order->total, 0, ',', ' ');
    $paid   = number_format($order->amount_paid, 0, ',', ' ');
    $reste  = number_format($order->balance, 0, ',', ' ');
    $statut = match($order->payment_status) { 'paid'=>'Soldé ✓', 'partial'=>'Acompte versé', default=>'Non payé' };

    if ($type === 'custom') {
        $details = "🧵 Vêtement : " . ucfirst($order->garment_type);
        if ($order->model_name) $details .= " ({$order->model_name})";
        $invoiceUrl = route('custom-orders.fiche', $order);
    } else {
        $items = $order->items->map(fn($i) => "  • {$i->product_name} x{$i->quantity}")->implode("\n");
        $details = "🛍 Articles :\n" . $items;
        $invoiceUrl = route('orders.invoice', $order);
    }

    $waMsg = urlencode(
        "Bonjour {$client->first_name} 👋\n\n" .
        "Voici le résumé de votre commande chez *GSIT Haute Couture* :\n\n" .
        "📋 Référence : {$ref}\n" .
        "{$details}\n\n" .
        "💰 Total : {$total} FCFA\n" .
        "✅ Payé : {$paid} FCFA\n" .
        ($order->balance > 0 ? "⏳ Reste : {$reste} FCFA\n" : "") .
        "📌 Statut : {$statut}\n\n" .
        "Merci pour votre confiance ! 🙏"
    );

    $emailSubject = urlencode("Votre reçu GSIT Haute Couture — {$ref}");
    $emailBody    = urlencode(
        "Bonjour {$client->first_name},\n\n" .
        "Merci pour votre commande chez GSIT Haute Couture.\n\n" .
        "Référence : {$ref}\n" .
        "Total : {$total} FCFA\n" .
        "Montant payé : {$paid} FCFA\n" .
        ($order->balance > 0 ? "Reste à payer : {$reste} FCFA\n" : "") .
        "Statut paiement : {$statut}\n\n" .
        "À très bientôt !\nL'équipe GSIT Haute Couture"
    );
@endphp

<div class="flex items-center gap-2 flex-wrap">
    @if($phone)
    <a href="https://wa.me/{{ $phone }}?text={{ $waMsg }}" target="_blank"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition-colors shadow-sm">
        <i data-lucide="message-circle" style="width:14px;height:14px"></i>
        WhatsApp
    </a>
    @endif
    @if($client->email)
    <a href="mailto:{{ $client->email }}?subject={{ $emailSubject }}&body={{ $emailBody }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition-colors shadow-sm">
        <i data-lucide="mail" style="width:14px;height:14px"></i>
        Email
    </a>
    @endif
    <a href="{{ $invoiceUrl }}" target="_blank"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
        <i data-lucide="file-text" style="width:14px;height:14px"></i>
        Reçu PDF
    </a>
</div>
