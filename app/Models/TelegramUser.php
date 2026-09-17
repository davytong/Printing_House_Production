<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_user_id',
        'first_name',
        'last_name',
        'username',
        'display_name',
        'source',
        'last_chat_title',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    /**
     * Record or update a user from Telegram payload.
     */
    public static function capture(array $from, ?string $chatTitle = null, string $source = 'message'): ?self
    {
        if (empty($from['id']) || !empty($from['is_bot'])) {
            return null;
        }

        $userId    = (string) $from['id'];
        $firstName = trim($from['first_name'] ?? '');
        $lastName  = trim($from['last_name'] ?? '');
        $username  = trim($from['username'] ?? '') ?: null;

        $fullName = trim("{$firstName} {$lastName}");
        $displayName = $fullName ?: ($username ? "@{$username}" : "User #{$userId}");

        return static::updateOrCreate(
            ['telegram_user_id' => $userId],
            [
                'first_name'      => $firstName ?: null,
                'last_name'       => $lastName ?: null,
                'username'        => $username,
                'display_name'    => $displayName,
                'source'          => $source,
                'last_chat_title' => $chatTitle,
                'last_seen_at'    => now(),
            ]
        );
    }
}