<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $fillable = [
        'code', 'name', 'name_km', 'category', 'sub_type', 'size',
        'unit', 'min_stock', 'location', 'unit_cost',
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
     * Calculate current stock from movements (never static!)
     */
    public function currentStock(): float
    {
        $in  = (float) $this->movements()->where('type', 'in')->sum('quantity');
        $out = (float) $this->movements()->where('type', 'out')->sum('quantity');

        // Adjustments set absolute value — get the latest one
        $lastAdjust = $this->movements()
            ->where('type', 'adjust')
            ->latest('movement_date')
            ->first();

        if ($lastAdjust) {
            // After last adjustment, only count movements AFTER it
            $afterAdj = $this->movements()
                ->where('created_at', '>', $lastAdjust->created_at)
                ->get();

            $adjIn  = $afterAdj->where('type', 'in')->sum('quantity');
            $adjOut = $afterAdj->where('type', 'out')->sum('quantity');

            return (float) $lastAdjust->quantity + $adjIn - $adjOut;
        }

        return $in - $out;
    }

    public function isLowStock(): bool
    {
        return $this->currentStock() <= (float) $this->min_stock;
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
