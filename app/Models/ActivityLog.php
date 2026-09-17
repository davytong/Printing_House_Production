<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_name', 'position', 'action', 'module', 'details', 'ip_address'];

    /**
     * Log an activity for the current session user.
     * Guaranteed fail-safe: never interrupts business operations if log persistence encounters issues.
     */
    public static function record(string $action, ?string $details = null, string $module = 'general'): ?static
    {
        $name     = session('user_name', 'System');
        $position = session('user_position', 'system');

        $payload = [
            'user_name'  => $name,
            'position'   => $position,
            'action'     => $action,
            'details'    => $details,
            'ip_address' => request()->ip() ?? '127.0.0.1',
        ];

        try {
            return static::create(array_merge($payload, ['module' => $module]));
        } catch (\Throwable $e) {
            try {
                // Fallback attempt without 'module' if column does not exist or schema diverges
                return static::create($payload);
            } catch (\Throwable $inner) {
                \Illuminate\Support\Facades\Log::warning('ActivityLog::record failed to persist: ' . $inner->getMessage());
                return null;
            }
        }
    }

    /**
     * Scopes for audit filtering
     */
    public function scopeForModule($query, ?string $module)
    {
        if ($module && $module !== 'all') {
            return $query->where('module', $module);
        }
        return $query;
    }

    public function scopeSearch($query, ?string $search)
    {
        if ($search && trim($search) !== '') {
            $term = '%' . trim($search) . '%';
            return $query->where(function ($q) use ($term) {
                $q->where('action', 'like', $term)
                  ->orWhere('details', 'like', $term)
                  ->orWhere('user_name', 'like', $term)
                  ->orWhere('position', 'like', $term)
                  ->orWhere('ip_address', 'like', $term);
            });
        }
        return $query;
    }

    public function scopeDateRange($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        return $query;
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->latest()->limit($limit);
    }

    /**
     * Module visual metadata
     */
    public function getModuleBadgeAttribute(): array
    {
        return match ($this->module) {
            'production'  => ['label' => 'Production',   'bg' => '#4f46e5', 'icon' => 'bi-printer-fill'],
            'inventory'   => ['label' => 'Inventory',    'bg' => '#0ea5e9', 'icon' => 'bi-box-seam-fill'],
            'procurement' => ['label' => 'Procurement',  'bg' => '#10b981', 'icon' => 'bi-cart-check-fill'],
            'auth'        => ['label' => 'Security/Auth','bg' => '#f59e0b', 'icon' => 'bi-shield-lock-fill'],
            'telegram'    => ['label' => 'Telegram',     'bg' => '#0284c7', 'icon' => 'bi-telegram'],
            'settings'    => ['label' => 'Settings',     'bg' => '#8b5cf6', 'icon' => 'bi-gear-fill'],
            default       => ['label' => ucfirst($this->module ?: 'General'), 'bg' => '#64748b', 'icon' => 'bi-activity'],
        };
    }
}
