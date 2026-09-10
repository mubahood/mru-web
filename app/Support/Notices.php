<?php

namespace App\Support;

use App\Models\SiteNotice;
use Illuminate\Support\Collection;

/**
 * The notice strip's data, resolved once per request.
 *
 * The partial is included from the layout, so it runs on every page. Without
 * memoising, a page that renders the layout twice — or a test that hits several
 * routes — pays for the query each time. The table is tiny, but a query on
 * every page for something that changes weekly is a query worth not making.
 */
class Notices
{
    private static ?Collection $live = null;

    /** @return Collection<int, SiteNotice> */
    public static function live(): Collection
    {
        if (self::$live !== null) {
            return self::$live;
        }

        // Missing table means the migration has not run — a fresh clone, or a
        // deploy mid-flight. A notice strip is not worth a 500.
        try {
            self::$live = SiteNotice::live()->limit(5)->get();
        } catch (\Throwable) {
            self::$live = collect();
        }

        return self::$live;
    }

    /** Tests change the data under a single process; this lets them see it. */
    public static function forget(): void
    {
        self::$live = null;
    }
}
