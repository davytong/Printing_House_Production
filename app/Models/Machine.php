<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    protected $fillable = [
        'code', 'name', 'model', 'manufacturer', 'serial_number',
        'type', 'status', 'purchased_date', 'last_maintenance',
        'next_maintenance', 'maintenance_interval_days', 'notes',
    ];

    protected $casts = [
        'purchased_date'   => 'date',
        'last_maintenance' => 'date',
        'next_maintenance' => 'date',
    ];

    protected static function booted(): void
    {
        static::created(function (Machine $m) {
            if (! $m->code) {
                $m->updateQuietly(['code' => 'MCH-' . str_pad($m->id, 3, '0', STR_PAD_LEFT)]);
            }
        });
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function isMaintenanceDue(): bool
    {
        return $this->next_maintenance && $this->next_maintenance->isPast();
    }

    public function daysUntilMaintenance(): int
    {
        if (! $this->next_maintenance) {
            return 999;
        }
        return (int) now()->diffInDays($this->next_maintenance, false);
    }
}
