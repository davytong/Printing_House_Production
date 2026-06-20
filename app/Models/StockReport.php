<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReport extends Model
{
    protected $fillable = [
        'report_date', 'category', 'title', 'notes', 'image_path',
        'summary_data', 'telegram_sent', 'sent_at', 'created_by',
    ];

    protected $casts = [
        'report_date'  => 'date',
        'summary_data' => 'array',
        'telegram_sent'=> 'boolean',
        'sent_at'      => 'datetime',
    ];

    /**
     * Human label for the report's category (null = all categories).
     */
    public function categoryLabel(): string
    {
        return match($this->category) {
            'paper'      => '📄 ក្រដាស',
            'film'       => '🎞️ Film',
            'offset'     => '🖨️ Offset',
            'consumable' => '🧴 Consumable (សម្ភារៈប្រើប្រាស់)',
            default      => '📦 ទាំងអស់',
        };
    }
}
