<?php

namespace App\Services;

use App\Models\Material;
use App\Models\StockMovement;
use Illuminate\Support\Collection;

class StockService
{
    /**
     * Record a stock movement and return the new current stock.
     */
    public function recordMovement(
        Material $material,
        string $type,
        float $quantity,
        ?string $reference = null,
        ?string $performedBy = null,
        ?string $notes = null,
        ?string $date = null,
    ): StockMovement {
        return StockMovement::create([
            'material_id'   => $material->id,
            'type'          => $type,
            'quantity'      => $quantity,
            'reference'     => $reference,
            'performed_by'  => $performedBy,
            'notes'         => $notes,
            'movement_date' => $date ?? now()->toDateString(),
        ]);
    }

    /**
     * Get current stock for all active materials.
     */
    public function getAllStockLevels(): Collection
    {
        return Material::where('status', 'active')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn(Material $m) => [
                'id'           => $m->id,
                'code'         => $m->code,
                'name'         => $m->name,
                'category'     => $m->category,
                'sub_type'     => $m->sub_type,
                'size'         => $m->size,
                'unit'         => $m->unit,
                'current_stock'=> $m->currentStock(),
                'min_stock'    => (float) $m->min_stock,
                'is_low'       => $m->isLowStock(),
                'location'     => $m->location,
                'unit_cost'    => (float) $m->unit_cost,
            ]);
    }

    /**
     * Get low-stock materials only.
     */
    public function getLowStockMaterials(): Collection
    {
        return Material::where('status', 'active')
            ->get()
            ->filter(fn(Material $m) => $m->isLowStock());
    }

    /**
     * Get stock summary by category.
     */
    public function getSummaryByCategory(): array
    {
        $materials = Material::where('status', 'active')->get();
        $summary   = [];

        foreach (['paper', 'film', 'offset', 'consumable'] as $cat) {
            $catMaterials = $materials->where('category', $cat);
            $summary[$cat] = [
                'total_items'   => $catMaterials->count(),
                'total_value'   => $catMaterials->sum(fn($m) => $m->currentStock() * (float) $m->unit_cost),
                'low_stock'     => $catMaterials->filter(fn($m) => $m->isLowStock())->count(),
                'out_of_stock'  => $catMaterials->filter(fn($m) => $m->currentStock() <= 0)->count(),
            ];
        }

        return $summary;
    }

    /**
     * Get today's movements.
     */
    public function getTodayMovements(): Collection
    {
        return StockMovement::with('material')
            ->where('movement_date', now()->toDateString())
            ->latest()
            ->get();
    }
}
