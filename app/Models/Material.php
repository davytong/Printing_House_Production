<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $fillable = [
        'code', 'name', 'name_km', 'category', 'sub_type', 'size',
        'unit', 'min_stock', 'critical_stock', 'location', 'unit_cost',
        'status', 'last_alerted_at', 'notes', 'icon',
    ];

    /**
     * Display label: "English — Khmer" or just English if no Khmer set.
     */
    public function displayName(): string
    {
        return $this->name_km
            ? "{$this->name} — {$this->name_km}"
            : $this->name;
    }

    protected $casts = [
        'min_stock'      => 'decimal:2',
        'critical_stock' => 'decimal:2',
        'unit_cost'      => 'decimal:2',
        'last_alerted_at'=> 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (Material $m) {
            if (! $m->code) {
                $prefix = match($m->category) {
                    'paper'       => 'PAP',
                    'film'        => 'FLM',
                    'offset'      => 'OFS',
                    'consumable'  => 'CON',
                    default       => 'MAT',
                };
                $m->updateQuietly(['code' => $prefix . '-' . str_pad($m->id, 4, '0', STR_PAD_LEFT)]);
            }
        });
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Calculate current stock from movements.
     * Loads this material's movements in ONE query, then computes in PHP.
     */
    public function currentStock(): float
    {
        return self::currentStockFromMovements($this->movements()->get());
    }

    /**
     * Compute current stock from an already-loaded movements collection.
     * Use this in loops (with movements eager-loaded/grouped) to avoid N+1.
     *
     * Logic: if there is an "adjust" movement, the latest one sets an absolute
     * value, and only in/out movements created AFTER it are added/subtracted.
     */
    public static function currentStockFromMovements($movements): float
    {
        $movements = $movements instanceof \Illuminate\Support\Collection
            ? $movements
            : collect($movements);

        // Latest adjustment (by movement_date, then created_at as tiebreak, then id)
        $lastAdjust = $movements->where('type', 'adjust')
            ->sortBy([
                ['movement_date', 'asc'],
                ['created_at', 'asc'],
                ['id', 'asc'],
            ])
            ->last();

        if ($lastAdjust) {
            $after  = $movements->filter(function ($mv) use ($lastAdjust) {
                if ($mv->created_at != $lastAdjust->created_at) {
                    return $mv->created_at > $lastAdjust->created_at;
                }
                return $mv->id > $lastAdjust->id;
            });
            $adjIn  = (float) $after->where('type', 'in')->sum('quantity');
            $adjOut = (float) $after->where('type', 'out')->sum('quantity');
            return (float) $lastAdjust->quantity + $adjIn - $adjOut;
        }

        $in  = (float) $movements->where('type', 'in')->sum('quantity');
        $out = (float) $movements->where('type', 'out')->sum('quantity');
        return $in - $out;
    }

    public function isLowStock(?float $stock = null): bool
    {
        $stock = $stock ?? $this->currentStock();
        return $stock <= (float) $this->min_stock;
    }

    public function stockStatus(?float $stock = null): string
    {
        $stock = $stock ?? $this->currentStock();
        if ($stock <= 0) {
            return 'OUT_OF_STOCK';
        }
        $critical = $this->critical_stock !== null ? (float) $this->critical_stock : null;
        if ($critical !== null && $stock <= $critical) {
            return 'CRITICAL';
        }
        if ($stock <= (float) $this->min_stock) {
            return 'LOW_STOCK';
        }
        return 'NORMAL';
    }

    public function stockStatusLabel(?float $stock = null): string
    {
        return match ($this->stockStatus($stock)) {
            'OUT_OF_STOCK' => 'អស់ស្តុក (Out of Stock)',
            'CRITICAL'     => 'ស្តុកសល់តិចខ្លាំង (Critical)',
            'LOW_STOCK'    => 'ស្តុកជិតអស់ (Low Stock)',
            'NORMAL'       => 'ធម្មតា (Normal)',
        };
    }

    public function stockStatusBadge(?float $stock = null): string
    {
        return match ($this->stockStatus($stock)) {
            'OUT_OF_STOCK' => '<span class="badge bg-dark text-white"><i class="bi bi-x-circle-fill me-1"></i> ⚫ អស់ស្តុក</span>',
            'CRITICAL'     => '<span class="badge bg-danger"><i class="bi bi-exclamation-octagon-fill me-1"></i> 🔴 Critical</span>',
            'LOW_STOCK'    => '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle-fill me-1"></i> 🟡 ជិតអស់</span>',
            'NORMAL'       => '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> 🟢 ធម្មតា</span>',
        };
    }

    public function categoryLabel(): string
    {
        return match($this->category) {
            'paper'      => '📄 ក្រដាស',
            'film'       => '🎞️ Film',
            'offset'     => '🖨️ Offset',
            'consumable' => '🧴 Consumable (សម្ភារៈប្រើប្រាស់)',
            default      => $this->category,
        };
    }

    public function categoryLabelShort(): string
    {
        return match($this->category) {
            'paper'      => 'ក្រដាស',
            'film'       => 'Film',
            'offset'     => 'Offset',
            'consumable' => 'Consumable (សម្ភារៈប្រើប្រាស់)',
            default      => $this->category,
        };
    }

    public function categoryEmoji(): string
    {
        return match($this->category) {
            'paper'      => '📄',
            'film'       => '🎞️',
            'offset'     => '🖨️',
            'consumable' => '🧴',
            default      => '📦',
        };
    }
}
