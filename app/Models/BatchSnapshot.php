<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchSnapshot extends Model
{
    protected $fillable = [
        'batch_id', 'book_id', 'title', 'grade', 'category',
        'target_qty', 'printed_qty',
    ];

    public function batch()
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id');
    }
}
