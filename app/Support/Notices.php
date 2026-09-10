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

    /**
     * The ticker notices, to be run in one lane.
     *
     * A real news ticker puts several items in a single moving band. Rendering
     * each as its own row instead would stack 32px strips until the strip is
     * taller than the header it sits above — two notices and the saving from
     * making it compact is already gone.
     *
     * @return Collection<int, SiteNotice>
     */
    public static function ticker(): Collection
    {
        return self::live()->filter(fn (SiteNotice $n) => $n->templateName() === 'ticker')->values();
    }

    /** Everything that is not a ticker keeps its own row. @return Collection<int, SiteNotice> */
    public static function rows(): Collection
    {
        return self::live()->reject(fn (SiteNotice $n) => $n->templateName() === 'ticker')->values();
    }

    /**
     * One key for the whole lane.
     *
     * Built from every notice in it, so editing or adding any of them brings
     * the lane back for a reader who dismissed the previous set.
     */
    public static function tickerKey(): string
    {
        return 'mru-ticker-'.substr(sha1(self::ticker()->map->dismissKey()->implode('|')), 0, 12);
    }

    /** A lane can only be dismissed if every notice in it allows it. */
    public static function tickerDismissible(): bool
    {
        $lane = self::ticker();

        return $lane->isNotEmpty() && $lane->every(fn (SiteNotice $n) => $n->is_dismissible);
    }

    /** Tests change the data under a single process; this lets them see it. */
    public static function forget(): void
    {
        self::$live = null;
    }
}
