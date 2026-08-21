<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionTaskProgress extends Model
{
    protected $table = 'production_task_progress';
    
    protected $fillable = [
        'year',
        'month',
        'day',
        'process',
        'task_name',
        'total_quantity',
        'printed_quantity',
        'note',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'day' => 'integer',
        'total_quantity' => 'integer',
        'printed_quantity' => 'integer',
    ];

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute()
    {
        if (!$this->total_quantity || $this->total_quantity <= 0) {
            return 0;
        }
        
        return min(100, round(($this->printed_quantity / $this->total_quantity) * 100, 1));
    }

    /**
     * Get remaining quantity
     */
    public function getRemainingQuantityAttribute()
    {
        if (!$this->total_quantity) {
            return 0;
        }
        
        return max(0, $this->total_quantity - $this->printed_quantity);
    }

    /**
     * Check if task is completed
     */
    public function getIsCompletedAttribute()
    {
        return $this->total_quantity && $this->printed_quantity >= $this->total_quantity;
    }
}
