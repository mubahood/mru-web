<?php

namespace App\Support;

/**
 * Typed access to the university.* settings JSON blobs seeded by
 * UniversityContentSeeder and the legacy importer. A missing or corrupt blob
 * degrades to [] so a page never fatals over copy.
 *
 * Deliberately uncached at this layer: `Settings::all()` already caches the
 * whole table behind Laravel's cache store and invalidates it on every write
 * (`Settings::flush()`). An earlier version kept a second, bare static-array
 * cache here with no invalidation of its own — harmless for a one-shot web
 * request, but it meant the first call in any longer-lived PHP process (an
 * artisan command, a queue worker taking more than one job, the test suite)
 * pinned that process to whatever the settings were at that first call,
 * forever. A save made in between was invisible to it. Reading through to
 * `Settings::get()` every time costs an array lookup against an
 * already-cached blob, not a query.
 */
class University
{
    /**
     * The University's public WhatsApp group.
     *
     * The last resort behind the admin-editable `whatsapp_link` contact
     * setting. It lived as a different hard-coded wa.me number in four
     * templates and the seeder, which is exactly how five places end up
     * disagreeing about one phone number.
     */
    public const WHATSAPP_GROUP = 'https://chat.whatsapp.com/JeS0v2R0UV6Dy4TJCtgj7d';

    public static function get(string $section): array
    {
        $decoded = json_decode((string) Settings::get("university.$section", '[]'), true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function identity(): array
    {
        return self::get('identity');
    }

    public static function contacts(): array
    {
        return self::get('contacts');
    }

    public static function links(): array
    {
        return self::get('links');
    }

    public static function applyUrl(): string
    {
        return self::links()['apply'] ?? 'https://eportal.mru.ac.ug/apply';
    }
}
