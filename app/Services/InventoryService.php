<?php

namespace App\Services;

use App\Models\Food;
use App\Models\InventoryTransaction;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Deduct stock for an item (e.g. sale, waste)
     */
    public function deductStock(
        Food $food,
        float $quantity,
        string $type,
        ?Model $reference = null,
        ?User $user = null,
        ?string $notes = null
    ): InventoryTransaction {
        return DB::transaction(function () use ($food, $quantity, $type, $reference, $user, $notes) {
            $openingStock = (float) $food->current_stock;
            $newStock = max(0, $openingStock - $quantity);

            $food->update(['current_stock' => $newStock]);

            $transaction = InventoryTransaction::create([
                'food_id' => $food->id,
                'user_id' => $user?->id,
                'type' => $type,
                'quantity' => -abs($quantity),
                'unit_cost' => (float) $food->cost_price,
                'opening_stock' => $openingStock,
                'closing_stock' => $newStock,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'notes' => $notes,
            ]);

            $this->checkLowStockAlert($food);

            return $transaction;
        });
    }

    /**
     * Add stock for an item (e.g. purchase, supplier delivery)
     */
    public function addStock(
        Food $food,
        float $quantity,
        float $unitCost,
        ?Model $reference = null,
        ?User $user = null,
        ?string $notes = null
    ): InventoryTransaction {
        return DB::transaction(function () use ($food, $quantity, $unitCost, $reference, $user, $notes) {
            $openingStock = (float) $food->current_stock;
            $newStock = $openingStock + abs($quantity);

            // Update cost price if provided
            $updates = ['current_stock' => $newStock];
            if ($unitCost > 0) {
                $updates['cost_price'] = $unitCost;
            }

            $food->update($updates);

            return InventoryTransaction::create([
                'food_id' => $food->id,
                'user_id' => $user?->id,
                'type' => 'purchase',
                'quantity' => abs($quantity),
                'unit_cost' => $unitCost,
                'opening_stock' => $openingStock,
                'closing_stock' => $newStock,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Adjust stock manually
     */
    public function adjustStock(
        Food $food,
        float $newStock,
        ?User $user = null,
        ?string $notes = null
    ): InventoryTransaction {
        return DB::transaction(function () use ($food, $newStock, $user, $notes) {
            $openingStock = (float) $food->current_stock;
            $diff = $newStock - $openingStock;

            $food->update(['current_stock' => $newStock]);

            $transaction = InventoryTransaction::create([
                'food_id' => $food->id,
                'user_id' => $user?->id,
                'type' => 'adjustment',
                'quantity' => $diff,
                'unit_cost' => (float) $food->cost_price,
                'opening_stock' => $openingStock,
                'closing_stock' => $newStock,
                'notes' => $notes ?? 'Manual inventory audit/adjustment',
            ]);

            $this->checkLowStockAlert($food);

            return $transaction;
        });
    }

    /**
     * Check if stock reached low threshold and trigger notification
     */
    public function checkLowStockAlert(Food $food): void
    {
        if ($food->current_stock <= $food->min_stock) {
            // Check if notification already exists today for this food
            $exists = Notification::where('type', 'low_stock')
                ->where('title', 'like', "%{$food->name}%")
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if (! $exists) {
                Notification::create([
                    'type' => 'low_stock',
                    'title' => "⚠️ Low Stock: {$food->name}",
                    'message' => "Stock for {$food->name} is at {$food->current_stock} {$food->unit}(s), below minimum of {$food->min_stock}.",
                    'link' => route('inventory.index'),
                    'is_read' => false,
                ]);
            }
        }
    }
}
