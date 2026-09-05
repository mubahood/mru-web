<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the inherited portfolio platform's commercial surface — the source-code
 * shop, the basket, checkout and the hire journey.
 *
 * This codebase serves two products from one application. On the university
 * domain those pages are not merely irrelevant: "Source code for sale, UGX
 * 650,000" published under the university's brand and listed in its sitemap is
 * a credibility problem. They are switched off by configuration rather than
 * deleted, so the same code still serves the portfolio domain unchanged.
 *
 * A 404 (not a redirect) is deliberate: on this domain the page genuinely does
 * not exist, which is what a search engine should be told.
 *
 * The routes stay *registered* even when disabled. Several shared views —
 * the sign-in page, the news index, the client portal — call route('hire') and
 * route('shop.index'), and an unregistered name throws
 * RouteNotFoundException. Registering the route and refusing to serve it keeps
 * URL generation total while making the page unreachable.
 */
class EnsureCommerceEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('features.commerce'), 404);

        return $next($request);
    }
}
