<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Who and what the site measures.
 *
 * The first-party tracker already decided this: the back office is not
 * audience, and a signed-in administrator editing all day is the largest single
 * source of fake traffic. Google Analytics is bolted on beside it, and if it
 * did not obey the same rules the two systems would disagree permanently — one
 * excluding /admin, the other counting every save as a page view.
 *
 * So the rules live here once, and both read them.
 */
class AnalyticsScope
{
    /** Should this request be measured at all? */
    public static function measurable(Request $request): bool
    {
        if (! config('analytics.enabled', true)) {
            return false;
        }

        foreach ((array) config('analytics.ignore_paths', []) as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        return ! (config('analytics.ignore_admins', true) && $request->user()?->isAdmin());
    }

    /**
     * The GA4 measurement id, or null when nothing should be sent.
     *
     * Null covers three separate cases and deliberately does not distinguish
     * them at the call site: no id configured, analytics switched off, or a
     * request nobody wants counted.
     */
    public static function googleId(Request $request): ?string
    {
        $id = trim((string) config('analytics.google.id'));

        if ($id === '') {
            return null;
        }

        if (config('analytics.google.respect_exclusions', true) && ! self::measurable($request)) {
            return null;
        }

        return $id;
    }
}
