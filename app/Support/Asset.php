<?php

namespace App\Support;

/** Cache-busted URLs for the stylesheets and scripts served from public/. */
class Asset
{
    /**
     * A versioned URL for a file under the document root.
     *
     * The templates used to call filemtime(public_path(...)) inline. That
     * throws when the file is not where it expects — and in the deployed
     * layout it is not: the framework lives in ~/laravel and the document root
     * is ~/public_html, so public_path() resolves somewhere that does not
     * exist unless index.php has called usePublicPath() first. A stylesheet
     * URL is not worth a 500, so a missing file degrades to the plain
     * unversioned URL and the page still renders.
     *
     * The stat is memoised: three layouts ask for the same file on the same
     * request, and this is called on every page of the site.
     *
     * @param  string  $path  relative to the document root, e.g. 'css/mru.css'
     */
    public static function versioned(string $path): string
    {
        static $stamps = [];

        if (! array_key_exists($path, $stamps)) {
            $full = public_path($path);
            $stamps[$path] = is_file($full) ? filemtime($full) : null;
        }

        $url = asset($path);

        return $stamps[$path] ? $url.'?v='.$stamps[$path] : $url;
    }
}
