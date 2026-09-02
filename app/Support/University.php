<?php

namespace App\Support;

/**
 * Typed access to the university.* settings JSON blobs seeded by
 * UniversityContentSeeder and the legacy importer. One decode per request per
 * key; a missing or corrupt blob degrades to [] so a page never fatals over
 * copy.
 */
class University
{
    /** @var array<string,array> */
    private static array $cache = [];

    public static function get(string $section): array
    {
        if (! array_key_exists($section, self::$cache)) {
            $decoded = json_decode((string) Settings::get("university.$section", '[]'), true);
            self::$cache[$section] = is_array($decoded) ? $decoded : [];
        }

        return self::$cache[$section];
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
