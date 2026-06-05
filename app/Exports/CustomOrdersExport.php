<?php

namespace App\Exports;

use App\Models\CustomOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CustomOrdersExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle
{
    public function __construct(private array $filters = []) {}

    public function title(): string { return 'Commandes Sur Mesure'; }

    public function collection()
    {
        $query = CustomOrder::with(['client', 'couturier', 'cashier'])->latest();

        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function($q) use ($s) {
                $q->where('reference', 'like', "%$s%")
                  ->orWhereHas('client', fn($c) =>
                      $c->where('first_name','like',"%$s%")
                        ->orWhere('last_name','like',"%$s%")
                        ->orWhere('phone','like',"%$s%")
                  );
            });
        }
        if (!empty($this->filters['status']))
            $query->where('status', $this->filters['status']);
        if (!empty($this->filters['couturier']))
            $query->where('assigned_to', $this->filters['couturier']);

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Référence', 'Date', 'Client', 'Téléphone', 'Genre',
            'Vêtement', 'Modèle', 'Couturier',
            'Tissu (FCFA)', 'Main d\'œuvre (FCFA)', 'Accessoires (FCFA)', 'Total (FCFA)',
            'Acompte (FCFA)', 'Reste (FCFA)', 'Paiement', 'Statut', 'Livraison',
        ];
    }

    public function map($order): array
    {
        return [
            $order->reference,
            $order->created_at->format('d/m/Y'),
            $order->client->full_name,
            $order->client->phone,
            ucfirst($order->gender),
            ucfirst($order->garment_type),
            $order->model_name ?? '—',
            $order->couturier?->name ?? 'Non assigné',
            number_format($order->fabric_cost, 0, ',', ' '),
            number_format($order->labor_cost, 0, ',', ' '),
            number_format($order->accessories_cost, 0, ',', ' '),
            number_format($order->total, 0, ',', ' '),
            number_format($order->amount_paid, 0, ',', ' '),
            number_format($order->balance, 0, ',', ' '),
            match($order->payment_status) { 'paid'=>'Soldé','partial'=>'Partiel',default=>'Impayé' },
            $order->getStatusLabel(),
            $order->delivery_date?->format('d/m/Y') ?? '—',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A'=>26,'B'=>12,'C'=>22,'D'=>16,'E'=>10,
            'F'=>14,'G'=>22,'H'=>20,
            'I'=>16,'J'=>18,'K'=>18,'L'=>16,
            'M'=>16,'N'=>14,'O'=>12,'P'=>18,'Q'=>14,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold'=>true,'color'=>['rgb'=>'FFFFFF']],
                'fill' => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'7C3AED']],
                'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
