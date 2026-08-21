<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** In-request cache so repeated Setting::get() calls don't re-hit the DB. */
    protected static array $cache = [];
    protected static bool $loaded = false;

    /** Load all settings once per request into the static cache. */
    protected static function loadAll(): void
    {
        if (static::$loaded) {
            return;
        }
        static::$cache = static::query()->pluck('value', 'key')->all();
        static::$loaded = true;
    }

    public static function get(string $key, $default = null)
    {
        static::loadAll();
        return array_key_exists($key, static::$cache) ? static::$cache[$key] : $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        // Keep the in-request cache in sync
        static::$cache[$key] = $value;
    }
}
