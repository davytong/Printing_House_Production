<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramGroup extends Model
{
    use HasFactory;

    protected $table = 'telegram_groups';

    protected $fillable = [
        'chat_id',
        'name',
        'type',
        'message_thread_id',
        'topic_name',
        'is_forum',
        'purpose',
    ];

    protected $casts = [
        'is_forum'          => 'boolean',
        'message_thread_id' => 'integer',
    ];

    /**
     * Display label: "Group Name › Topic" or just "Group Name"
     */
    public function displayLabel(): string
    {
        return $this->topic_name
            ? "{$this->name} › {$this->topic_name}"
            : $this->name;
    }

    /**
     * Find the assigned group/topic for a specific purpose.
     * Purposes: paper_stock, press_report, finishing_report, consumable_stock, general, procurement
     */
    public static function forPurpose(string $purpose): ?self
    {
        return static::where('purpose', $purpose)->first();
    }

    /**
     * Get purpose for a stock category (used by daily report).
     */
    public static function forCategory(string $category): ?self
    {
        $purpose = match($category) {
            'paper'      => 'paper_stock',
            'film'       => 'finishing_report',
            'consumable' => 'consumable_stock',
            default      => 'general',
        };

        return static::forPurpose($purpose) ?? static::forPurpose('general');
    }
}
