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
     * Loads every material's movements in a single query (no N+1).
     */
    public function getAllStockLevels(): Collection
    {
        $materials = Material::where('status', 'active')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $movesByMaterial = StockMovement::whereIn('material_id', $materials->pluck('id'))
            ->get()
            ->groupBy('material_id');

        return $materials->map(function (Material $m) use ($movesByMaterial) {
            $moves = $movesByMaterial->get($m->id, collect());
            $stock = Material::currentStockFromMovements($moves);
            return [
                'id'             => $m->id,
                'code'           => $m->code,
                'name'           => $m->name,
                'name_km'        => $m->name_km,
                'category'       => $m->category,
                'sub_type'       => $m->sub_type,
                'size'           => $m->size,
                'unit'           => $m->unit,
                'current_stock'  => $stock,
                'min_stock'      => (float) $m->min_stock,
                'critical_stock' => $m->critical_stock !== null ? (float) $m->critical_stock : null,
                'is_low'         => $m->isLowStock($stock),
                'stock_status'   => $m->stockStatus($stock),
                'status_label'   => $m->stockStatusLabel($stock),
                'status_badge'   => $m->stockStatusBadge($stock),
                'location'       => $m->location,
                'unit_cost'      => (float) $m->unit_cost,
            ];
        });
    }

    /**
     * Get low-stock materials only (single query for movements).
     */
    public function getLowStockMaterials(): Collection
    {
        $materials = Material::where('status', 'active')->get();

        $movesByMaterial = StockMovement::whereIn('material_id', $materials->pluck('id'))
            ->get()
            ->groupBy('material_id');

        return $materials->filter(function (Material $m) use ($movesByMaterial) {
            $stock = Material::currentStockFromMovements($movesByMaterial->get($m->id, collect()));
            $m->calculated_stock = $stock;
            return $m->isLowStock($stock);
        })->values();
    }

    /**
     * Get stock summary by category (single query for movements).
     */
    public function getSummaryByCategory(): array
    {
        $materials = Material::where('status', 'active')->get();

        $movesByMaterial = StockMovement::whereIn('material_id', $materials->pluck('id'))
            ->get()
            ->groupBy('material_id');

        // Precompute stock once per material
        $stockById = [];
        foreach ($materials as $m) {
            $stockById[$m->id] = Material::currentStockFromMovements($movesByMaterial->get($m->id, collect()));
        }

        $summary = [];
        foreach (['paper', 'film', 'offset', 'consumable'] as $cat) {
            $catMaterials = $materials->where('category', $cat);
            $summary[$cat] = [
                'total_items'  => $catMaterials->count(),
                'total_value'  => $catMaterials->sum(fn($m) => $stockById[$m->id] * (float) $m->unit_cost),
                'low_stock'    => $catMaterials->filter(fn($m) => $stockById[$m->id] <= (float) $m->min_stock)->count(),
                'out_of_stock' => $catMaterials->filter(fn($m) => $stockById[$m->id] <= 0)->count(),
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
