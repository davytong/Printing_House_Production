<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionBatch extends Model
{
    protected $fillable = ['name', 'status', 'notes', 'started_at', 'completed_at'];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function snapshots()
    {
        return $this->hasMany(BatchSnapshot::class, 'batch_id');
    }

    public function books()
    {
        return $this->hasMany(Book::class, 'batch_id');
    }

    /**
     * Get the current active batch, creating one if none exists.
     */
    public static function current(): self
    {
        $batch = static::where('status', 'active')->latest('id')->first();

        if (!$batch) {
            $count = static::count();
            $batch = static::create([
                'name'       => 'Batch ' . ($count + 1),
                'status'     => 'active',
                'started_at' => now(),
            ]);

            // Attach all existing books to this first batch
            Book::whereNull('batch_id')->update(['batch_id' => $batch->id]);
        }

        return $batch;
    }
}
