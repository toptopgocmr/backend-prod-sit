<?php

namespace App\Services;

use App\Models\{Product, StockMovement};
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Entrée en stock (réception marchandise)
     */
    public function addStock(int $productId, float $quantity, string $reason = '', ?string $reference = null, float $unitCost = 0): StockMovement
    {
        return DB::transaction(function () use ($productId, $quantity, $reason, $reference, $unitCost) {
            $product = Product::lockForUpdate()->findOrFail($productId);
            $before  = $product->getCurrentStock();

            if ($product->type === 'tissu') {
                $product->increment('available_meters', $quantity);
            } else {
                $product->increment('stock_quantity', $quantity);
            }

            $product->refresh();
            $after = $product->getCurrentStock();

            return StockMovement::create([
                'product_id'      => $productId,
                'user_id'         => auth()->id(),
                'type'            => 'entree',
                'quantity'        => $quantity,
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'unit_cost'       => $unitCost,
                'reference'       => $reference,
                'reason'          => $reason,
            ]);
        });
    }

    /**
     * Sortie de stock (vente, utilisation)
     */
    public function deduct(int $productId, float $quantity, string $reason = '', ?string $modelType = null, ?int $modelId = null): StockMovement
    {
        return DB::transaction(function () use ($productId, $quantity, $reason, $modelType, $modelId) {
            $product = Product::lockForUpdate()->findOrFail($productId);
            $before  = $product->getCurrentStock();

            if ($before < $quantity) {
                throw new \Exception("Stock insuffisant pour {$product->name}. Disponible: {$before} {$product->getStockUnit()}");
            }

            if ($product->type === 'tissu') {
                $product->decrement('available_meters', $quantity);
            } else {
                $product->decrement('stock_quantity', $quantity);
            }

            $product->refresh();
            $after = $product->getCurrentStock();

            $movement = StockMovement::create([
                'product_id'      => $productId,
                'user_id'         => auth()->id(),
                'type'            => 'sortie',
                'quantity'        => $quantity,
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'reason'          => $reason,
                'movable_type'    => $modelType,
                'movable_id'      => $modelId,
            ]);

            // Alerte stock faible
            if ($product->isLowStock()) {
                // Trigger notification
                event(new \App\Events\LowStockAlert($product));
            }

            return $movement;
        });
    }

    /**
     * Ajustement d'inventaire
     */
    public function adjust(int $productId, float $newQuantity, string $reason): StockMovement
    {
        return DB::transaction(function () use ($productId, $newQuantity, $reason) {
            $product = Product::lockForUpdate()->findOrFail($productId);
            $before  = $product->getCurrentStock();
            $diff    = $newQuantity - $before;

            if ($product->type === 'tissu') {
                $product->update(['available_meters' => $newQuantity]);
            } else {
                $product->update(['stock_quantity' => $newQuantity]);
            }

            return StockMovement::create([
                'product_id'      => $productId,
                'user_id'         => auth()->id(),
                'type'            => 'ajustement',
                'quantity'        => abs($diff),
                'quantity_before' => $before,
                'quantity_after'  => $newQuantity,
                'reason'          => $reason,
            ]);
        });
    }

    /**
     * Rapport stock critique
     */
    public function getLowStockReport(): \Illuminate\Database\Eloquent\Collection
    {
        return Product::active()
            ->with('category')
            ->where(function ($q) {
                $q->where(function($q2) {
                    $q2->where('type', 'tissu')
                       ->whereRaw('available_meters <= alert_threshold');
                })->orWhere(function($q2) {
                    $q2->where('type', '!=', 'tissu')
                       ->whereRaw('stock_quantity <= alert_threshold');
                });
            })
            ->orderByRaw('(CASE WHEN type="tissu" THEN available_meters ELSE stock_quantity END) ASC')
            ->get();
    }
}
