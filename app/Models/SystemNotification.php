<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    protected $table    = 'system_notifications';
    protected $fillable = [
        'type', 'module', 'title', 'message',
        'action_url', 'is_read', 'telegram_sent', 'read_at',
    ];

    protected $casts = [
        'is_read'       => 'boolean',
        'telegram_sent' => 'boolean',
        'read_at'       => 'datetime',
    ];

    public static function notify(
        string $type,
        string $module,
        string $title,
        string $message,
        ?string $actionUrl = null
    ): static {
        return static::create([
            'type'       => $type,
            'module'     => $module,
            'title'      => $title,
            'message'    => $message,
            'action_url' => $actionUrl,
        ]);
    }

    public function markRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }
}
