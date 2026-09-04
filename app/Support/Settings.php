<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Thin, cache-backed accessor over the key/value `settings` table.
 *
 * Safe to call before the table exists (fresh install / early boot): every
 * lookup falls back to the supplied default. Categories, regulatory bodies and
 * validity periods live in their own tables. This holds site-wide config only
 * (name, tagline, contacts, languages, verify rate-limit, QR base URL).
 */
class Settings
{
    private const CACHE_KEY = 'app.settings';

    /**
     * Whether the table has been seen to exist. Memoised in one direction
     * only: once the table is there it will not disappear under a running
     * process, but a *missing* table is re-checked every time so a fresh
     * install still works the moment migrations finish. A homepage render
     * asks for settings ~16 times, and each was paying for its own
     * `information_schema` round trip.
     */
    private static bool $tableExists = false;

    /** @return array<string,mixed> */
    public static function all(): array
    {
        if (! self::$tableExists) {
            if (! Schema::hasTable('settings')) {
                return [];
            }
            self::$tableExists = true;
        }

        return Cache::rememberForever(self::CACHE_KEY, function () {
            return \App\Models\Setting::query()->pluck('value', 'key')->toArray();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all()[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        self::flush();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
