<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class OrdersExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle
{
    public function __construct(private array $filters = []) {}

    public function title(): string
    {
        return 'Ventes';
    }

    public function collection()
    {
        $query = Order::with(['client', 'cashier', 'items'])
            ->latest();

        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%$s%")
                  ->orWhereHas('client', fn($c) =>
                      $c->where('first_name', 'like', "%$s%")
                        ->orWhere('last_name', 'like', "%$s%")
                        ->orWhere('phone', 'like', "%$s%")
                  );
            });
        }
        if (!empty($this->filters['status']))
            $query->where('status', $this->filters['status']);
        if (!empty($this->filters['payment_status']))
            $query->where('payment_status', $this->filters['payment_status']);

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Référence',
            'Date',
            'Client',
            'Téléphone',
            'Type',
            'Articles',
            'Sous-total (FCFA)',
            'Remise (FCFA)',
            'Total (FCFA)',
            'Acompte (FCFA)',
            'Reste (FCFA)',
            'Paiement',
            'Statut',
            'Caissier',
        ];
    }

    public function map($order): array
    {
        $articles = $order->items->map(fn($i) =>
            $i->product_name . ' x' . $i->quantity
        )->implode(' | ');

        return [
            $order->reference,
            $order->created_at->format('d/m/Y'),
            $order->client->full_name,
            $order->client->phone,
            match($order->type) {
                'tissu'         => 'Tissu',
                'pret_a_porter' => 'Prêt-à-porter',
                default         => 'Mixte',
            },
            $articles,
            number_format($order->subtotal, 0, ',', ' '),
            number_format($order->discount ?? 0, 0, ',', ' '),
            number_format($order->total, 0, ',', ' '),
            number_format($order->amount_paid, 0, ',', ' '),
            number_format($order->balance ?? 0, 0, ',', ' '),
            match($order->payment_status) {
                'paid'    => 'Payé',
                'partial' => 'Partiel',
                default   => 'Impayé',
            },
            $order->getStatusLabel(),
            $order->cashier?->name ?? '—',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28, // Référence
            'B' => 12, // Date
            'C' => 24, // Client
            'D' => 16, // Téléphone
            'E' => 16, // Type
            'F' => 40, // Articles
            'G' => 18, // Sous-total
            'H' => 14, // Remise
            'I' => 16, // Total
            'J' => 16, // Acompte
            'K' => 14, // Reste
            'L' => 12, // Paiement
            'M' => 14, // Statut
            'N' => 20, // Caissier
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8820C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
