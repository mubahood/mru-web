<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Master switch
    |--------------------------------------------------------------------------
    | Off means nothing is written and nothing is served to the browser. The
    | admin screens keep working against whatever was collected before.
    */

    'enabled' => env('ANALYTICS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Your own visits
    |--------------------------------------------------------------------------
    | A one-person business editing its own site all day is the single largest
    | source of fake traffic in a system like this. Signed-in admins are left
    | out by default; set this false for a week if you want to watch your own
    | journey through the site while testing.
    */

    'ignore_admins' => env('ANALYTICS_IGNORE_ADMINS', true),

    /*
    |--------------------------------------------------------------------------
    | Paths never recorded
    |--------------------------------------------------------------------------
    | Request::is() patterns. The back office is here because measuring the
    | administration of a site tells you nothing about its audience.
    */

    'ignore_paths' => [
        'admin', 'admin/*',
        'livewire/*', 'telescope/*', 'horizon/*',
        'up', 'sitemap.xml', 'robots.txt', 'favicon.ico', 'sw.js', 'manifest.json',
        '_a', '_a/*',
        'storage/*', 'build/*', 'vendor/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    | Page views and events are the tables that grow without limit. The daily
    | rollup is what the long-range charts read, and it is never pruned, so
    | shortening this loses the ability to drill into an old day, not the
    | history of the numbers themselves.
    */

    'retain_page_views_days' => env('ANALYTICS_RETAIN_DAYS', 400),
    'retain_events_days' => env('ANALYTICS_RETAIN_EVENT_DAYS', 730),

    /*
    |--------------------------------------------------------------------------
    | Geolocation
    |--------------------------------------------------------------------------
    | Country arrives free when something in front of the app sets a header
    | (Cloudflare's CF-IPCountry, most cPanel GeoIP modules). Where nothing
    | does, `analytics:geolocate` can resolve the stored addresses in batches.
    |
    | That sends visitor IP addresses to a third party, so it is off unless you
    | turn it on. Nothing else in this module talks to the network.
    */

    'geo' => [
        'enabled' => env('ANALYTICS_GEO', false),
        'endpoint' => 'http://ip-api.com/batch',
        'batch' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Analytics (GA4)
    |--------------------------------------------------------------------------
    | Runs alongside the first-party analytics above rather than replacing it.
    | The two answer different questions: this one compares the site to the rest
    | of the web and survives a database restore; the first-party one knows what
    | a "programme page" is and never leaves the server.
    |
    | The id lives in the environment, not the markup. A staging copy with the
    | production id pollutes the real numbers, and the only way to notice is to
    | wonder why traffic doubled.
    |
    | `respect_exclusions` makes Google obey the same ignore_admins and
    | ignore_paths rules as the first-party tracker. Without it the back office
    | is deliberately excluded from one system and silently measured by the
    | other, and the two never agree.
    */

    'google' => [
        'id' => env('GA_MEASUREMENT_ID'),
        'respect_exclusions' => env('GA_RESPECT_EXCLUSIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Browser beacon
    |--------------------------------------------------------------------------
    | Reports reading time and scroll depth once a page is being left. Without
    | it every page view still counts; you simply lose "did they read it".
    */

    'beacon' => [
        'enabled' => env('ANALYTICS_BEACON', true),
        // A page open longer than this is a forgotten tab, not a reader.
        'max_seconds' => 3600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Live view
    |--------------------------------------------------------------------------
    */

    'online_window_minutes' => 5,
];
