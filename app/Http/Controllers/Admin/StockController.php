<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Product, StockMovement};
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Border, Fill, Font};
use Barryvdh\DomPDF\Facade\Pdf;

class StockController extends Controller
{
    public function __construct(private StockService $stockService) {}

    // ─────────────────────────────────────────────
    //  Pages principales
    // ─────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Product::active()->with('category');

        if ($request->filled('type'))   $query->where('type', $request->type);
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name',      'like', "%{$request->search}%")
                  ->orWhere('reference', 'like', "%{$request->search}%");
            });
        }

        $products = $query->orderBy('name')->paginate(20)->withQueryString();

        $stats = [
            'total_products' => Product::active()->count(),
            'low_stock'      => $this->stockService->getLowStockReport()->count(),
            'out_of_stock'   => Product::active()
                                    ->where(fn ($q) => $q->where('stock_quantity', 0)
                                                         ->orWhere('available_meters', 0))
                                    ->count(),
            'total_value'    => Product::active()
                                    ->selectRaw('SUM(CASE WHEN type="tissu"
                                                      THEN available_meters * cost_price
                                                      ELSE stock_quantity * cost_price END) as val')
                                    ->value('val') ?? 0,
        ];

        return view('stock.index', compact('products', 'stats'));
    }

    public function movements(Request $request)
    {
        $movements = StockMovement::with(['product', 'user'])->latest()->paginate(25);
        return view('stock.movements', compact('movements'));
    }

    public function lowStock()
    {
        $products = $this->stockService->getLowStockReport();
        return view('stock.low-stock', compact('products'));
    }

    public function inventory()
    {
        $products = Product::active()->with('category')->orderBy('type')->orderBy('name')->get();
        return view('stock.inventory', compact('products'));
    }

    // ─────────────────────────────────────────────
    //  Actions stock
    // ─────────────────────────────────────────────

    public function addStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|numeric|min:0.01',
            'reason'     => 'required|string|max:255',
            'reference'  => 'nullable|string|max:100',
            'unit_cost'  => 'nullable|numeric|min:0',
        ]);

        try {
            $this->stockService->addStock(
                $request->product_id,
                $request->quantity,
                $request->reason,
                $request->reference,
                $request->unit_cost ?? 0
            );
            return back()->with('success', 'Stock mis à jour avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function adjust(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'new_quantity' => 'required|numeric|min:0',
            'reason'       => 'required|string|max:255',
        ]);

        try {
            $this->stockService->adjust($request->product_id, $request->new_quantity, $request->reason);
            return back()->with('success', 'Inventaire ajusté avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    //  Export Excel — Stock complet
    // ─────────────────────────────────────────────

    public function exportExcel(Request $request)
    {
        $products = Product::active()->with('category')
                        ->orderBy('type')->orderBy('name')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stock');

        // ── En-tête document ──
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'RAPPORT DE STOCK — ' . now()->format('d/m/Y H:i'));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F97316']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // ── En-têtes colonnes ──
        $headers = ['Référence', 'Produit', 'Type', 'Catégorie', 'Stock actuel', 'Unité', 'Seuil alerte', 'État'];
        foreach ($headers as $col => $label) {
            $cell = chr(65 + $col) . '2';
            $sheet->setCellValue($cell, $label);
        }
        $sheet->getStyle('A2:H2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '6B7280']]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(22);

        // ── Données ──
        $row = 3;
        foreach ($products as $product) {
            $isTissu = $product->type === 'tissu';
            $stock   = $isTissu ? $product->available_meters : $product->stock_quantity;
            $unit    = $isTissu ? 'm' : 'pcs';

            if ($stock <= 0)                                     $etat = 'RUPTURE';
            elseif ($product->alert_threshold && $stock < $product->alert_threshold) $etat = 'Stock faible';
            else                                                 $etat = 'OK';

            $sheet->setCellValue("A{$row}", $product->reference);
            $sheet->setCellValue("B{$row}", $product->name);
            $sheet->setCellValue("C{$row}", $isTissu ? 'Tissu' : 'PAP / Article');
            $sheet->setCellValue("D{$row}", $product->category->name ?? '—');
            $sheet->setCellValue("E{$row}", $stock);
            $sheet->setCellValue("F{$row}", $unit);
            $sheet->setCellValue("G{$row}", $product->alert_threshold ?? '—');
            $sheet->setCellValue("H{$row}", $etat);

            // Couleur état
            $stateColor = match ($etat) {
                'RUPTURE'     => 'FEE2E2',  // rouge clair
                'Stock faible' => 'FEF3C7', // orange clair
                default        => 'DCFCE7', // vert clair
            };
            $sheet->getStyle("H{$row}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $stateColor]],
                'font'      => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Lignes alternées
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:G{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F9FAFB');
            }

            $sheet->getStyle("A{$row}:H{$row}")->getBorders()
                ->getAllBorders()->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setRGB('E5E7EB');

            $row++;
        }

        // ── Largeurs colonnes ──
        $widths = ['A' => 14, 'B' => 30, 'C' => 14, 'D' => 18, 'E' => 14, 'F' => 8, 'G' => 14, 'H' => 14];
        foreach ($widths as $col => $w) $sheet->getColumnDimension($col)->setWidth($w);

        // ── Freeze + filtre ──
        $sheet->freezePane('A3');
        $sheet->setAutoFilter("A2:H" . ($row - 1));

        // ── Onglet mouvements récents ──
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Mouvements récents');
        $this->buildMovementsSheet($sheet2);

        // ── Stream ──
        $writer   = new Xlsx($spreadsheet);
        $filename = 'stock_' . now()->format('Ymd_Hi') . '.xlsx';

        return Response::stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    private function buildMovementsSheet($sheet): void
    {
        $movements = StockMovement::with(['product', 'user'])->latest()->limit(200)->get();

        $headers = ['Date', 'Type', 'Produit', 'Référence produit', 'Quantité', 'Avant', 'Après', 'Raison', 'Utilisateur'];
        foreach ($headers as $col => $label) {
            $cell = chr(65 + $col) . '1';
            $sheet->setCellValue($cell, $label);
        }
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']],
        ]);

        $row = 2;
        foreach ($movements as $mv) {
            $isTissu = $mv->product?->type === 'tissu';
            $typeLabel = match ($mv->type) {
                'entree', 'in' => 'Entrée',
                'sortie', 'out' => 'Sortie',
                'adjustment'   => 'Ajustement',
                default        => ucfirst($mv->type),
            };

            $sheet->setCellValue("A{$row}", $mv->created_at->format('d/m/Y H:i'));
            $sheet->setCellValue("B{$row}", $typeLabel);
            $sheet->setCellValue("C{$row}", $mv->product?->name ?? 'Supprimé');
            $sheet->setCellValue("D{$row}", $mv->product?->reference ?? '—');
            $sheet->setCellValue("E{$row}", number_format($mv->quantity, $isTissu ? 1 : 0, ',', ' ') . ' ' . ($isTissu ? 'm' : 'pcs'));
            $sheet->setCellValue("F{$row}", $mv->quantity_before ?? '—');
            $sheet->setCellValue("G{$row}", $mv->quantity_after ?? '—');
            $sheet->setCellValue("H{$row}", $mv->reason ?? '—');
            $sheet->setCellValue("I{$row}", $mv->user?->name ?? 'Système');
            $row++;
        }

        foreach (['A' => 16, 'B' => 12, 'C' => 26, 'D' => 14, 'E' => 12, 'F' => 10, 'G' => 10, 'H' => 30, 'I' => 18] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }
    }

    // ─────────────────────────────────────────────
    //  Export PDF — Stock complet
    // ─────────────────────────────────────────────

    public function exportPdf(Request $request)
    {
        $products = Product::active()->with('category')
                        ->orderBy('type')->orderBy('name')->get();

        $stats = [
            'total'      => $products->count(),
            'low_stock'  => $products->filter(fn ($p) => $p->isLowStock())->count(),
            'rupture'    => $products->filter(fn ($p) => $p->getCurrentStock() == 0)->count(),
            'total_value'=> Product::active()
                               ->selectRaw('SUM(CASE WHEN type="tissu"
                                             THEN available_meters * cost_price
                                             ELSE stock_quantity * cost_price END) as val')
                               ->value('val') ?? 0,
        ];

        $pdf = Pdf::loadView('stock.exports.pdf-stock', compact('products', 'stats'))
                  ->setPaper('a4', 'landscape')
                  ->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);

        $filename = 'stock_' . now()->format('Ymd_Hi') . '.pdf';
        return $pdf->download($filename);
    }

    // ─────────────────────────────────────────────
    //  Export PDF — Stock faible uniquement
    // ─────────────────────────────────────────────

    public function exportLowStockPdf()
    {
        $products = $this->stockService->getLowStockReport();

        $pdf = Pdf::loadView('stock.exports.pdf-low-stock', compact('products'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);

        return $pdf->download('stock_faible_' . now()->format('Ymd_Hi') . '.pdf');
    }

    // ─────────────────────────────────────────────
    //  Export Excel — Mouvements
    // ─────────────────────────────────────────────

    public function exportMovementsExcel(Request $request)
    {
        $movements = StockMovement::with(['product', 'user'])->latest()->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Mouvements de stock');

        // En-tête
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'MOUVEMENTS DE STOCK — ' . now()->format('d/m/Y H:i'));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F97316']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $this->buildMovementsSheet($sheet); // réutilise la méthode privée (les headers sont à la ligne 1 ici)
        // On écrase la ligne 1 qui vient d'être faite par buildMovementsSheet
        // → On reconstruit proprement depuis la ligne 2
        // En réalité on appelle buildMovementsSheet qui commence à A1, donc on l'adapte :
        // Ici on réinitialise et on repart manuellement.

        // Reset et rebuild propre
        unset($spreadsheet);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Mouvements');
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'MOUVEMENTS DE STOCK — ' . now()->format('d/m/Y H:i'));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F97316']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $headers = ['Date', 'Type', 'Produit', 'Réf. produit', 'Quantité', 'Avant', 'Après', 'Raison', 'Utilisateur'];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue(chr(65 + $col) . '2', $label);
        }
        $sheet->getStyle('A2:I2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $row = 3;
        foreach ($movements as $mv) {
            $isTissu   = $mv->product?->type === 'tissu';
            $typeLabel = match ($mv->type) {
                'entree','in' => 'Entrée', 'sortie','out' => 'Sortie', 'adjustment' => 'Ajustement', default => ucfirst($mv->type),
            };
            $typeColor = match ($mv->type) {
                'entree','in' => '166534', 'sortie','out' => '991B1B', 'adjustment' => '1E40AF', default => '374151',
            };

            $sheet->setCellValue("A{$row}", $mv->created_at->format('d/m/Y H:i'));
            $sheet->setCellValue("B{$row}", $typeLabel);
            $sheet->setCellValue("C{$row}", $mv->product?->name ?? 'Produit supprimé');
            $sheet->setCellValue("D{$row}", $mv->product?->reference ?? '—');
            $sheet->setCellValue("E{$row}", number_format($mv->quantity, $isTissu ? 1 : 0, ',', ' ') . ' ' . ($isTissu ? 'm' : 'pcs'));
            $sheet->setCellValue("F{$row}", $mv->quantity_before !== null ? number_format($mv->quantity_before, $isTissu ? 1 : 0, ',', ' ') : '—');
            $sheet->setCellValue("G{$row}", $mv->quantity_after !== null ? number_format($mv->quantity_after, $isTissu ? 1 : 0, ',', ' ') : '—');
            $sheet->setCellValue("H{$row}", $mv->reason ?? '—');
            $sheet->setCellValue("I{$row}", $mv->user?->name ?? 'Système');

            $sheet->getStyle("B{$row}")->getFont()->setBold(true)->getColor()->setRGB($typeColor);

            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9FAFB');
            }
            $row++;
        }

        foreach (['A' => 16, 'B' => 12, 'C' => 26, 'D' => 14, 'E' => 12, 'F' => 10, 'G' => 10, 'H' => 32, 'I' => 18] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }
        $sheet->freezePane('A3');
        $sheet->setAutoFilter('A2:I' . ($row - 1));

        $writer   = new Xlsx($spreadsheet);
        $filename = 'mouvements_stock_' . now()->format('Ymd_Hi') . '.xlsx';

        return Response::stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
