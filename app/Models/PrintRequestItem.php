<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintRequestItem extends Model
{
    protected $fillable = [
        'print_request_id',
        'book_id',
        'book_title',
        'grade',
        'category',
        'quantity_requested',
        'notes',
    ];

    public function printRequest(): BelongsTo
    {
        return $this->belongsTo(PrintRequest::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function categoryLabel(): string
    {
        return match($this->category) {
            'perfect_binding' => 'បិតក្បាល',
            'staple'          => 'កិបកណ្ដាល',
            default           => $this->category ?? '—',
        };
    }
}
