<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Commercial surface (source-code shop, basket, checkout, hire journey)
    |--------------------------------------------------------------------------
    |
    | This application serves two products from one codebase: Muteesa I Royal
    | University, and the portfolio/product platform it was built from. The
    | commercial pages belong to the second and must not be reachable — or
    | indexed — on the university domain.
    |
    | Off by default so a fresh university deployment is correct without anyone
    | remembering to disable anything. Set FEATURE_COMMERCE=true on the
    | portfolio domain.
    |
    */

    'commerce' => (bool) env('FEATURE_COMMERCE', false),

];
