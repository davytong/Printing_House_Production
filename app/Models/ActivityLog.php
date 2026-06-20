<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_name', 'position', 'action', 'details', 'ip_address'];

    /**
     * Log an activity for the current session user.
     */
    public static function record(string $action, ?string $details = null): void
    {
        $name     = session('user_name', 'Unknown');
        $position = session('user_position', 'Unknown');

        static::create([
            'user_name'  => $name,
            'position'   => $position,
            'action'     => $action,
            'details'    => $details,
            'ip_address' => request()->ip(),
        ]);
    }
}
