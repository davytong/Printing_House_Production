<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['batch_id', 'title', 'category', 'printing_method', 'grade', 'target_qty', 'total_printed'];

    public function dailyPrints()
    {
        return $this->hasMany(DailyPrint::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id');
    }

    /**
     * Shared ordering: grade (numeric) → type (Textbook/Workbook/Song/Forktale)
     * → subject (Listening/Reading/Writing) → title.
     * Use this everywhere books are listed to keep ordering consistent.
     */
    public function scopeOrdered($query)
    {
        return $query
            ->orderByRaw("CAST(SUBSTRING_INDEX(COALESCE(grade,'ZZZ'), ' ', -1) AS UNSIGNED)")
            ->orderBy('grade')
            ->orderByRaw("CASE 
                WHEN title LIKE '%Textbook%' THEN 1
                WHEN title LIKE '%Workbook%' THEN 2
                WHEN title LIKE '%Song%' THEN 3
                WHEN title LIKE '%Forktale%' THEN 4
                ELSE 5
            END")
            ->orderByRaw("CASE 
                WHEN title LIKE 'Listening%' THEN 1
                WHEN title LIKE 'Reading%' THEN 2
                WHEN title LIKE 'Writing%' THEN 3
                ELSE 4
            END")
            ->orderBy('title');
    }
}
