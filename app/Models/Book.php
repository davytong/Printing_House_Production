<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['batch_id', 'title', 'category', 'grade', 'target_qty', 'total_printed'];

    public function dailyPrints()
    {
        return $this->hasMany(DailyPrint::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id');
    }
}
