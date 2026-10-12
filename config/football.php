<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Competition and season
    |--------------------------------------------------------------------------
    |
    | kizon-pl covers the Premier League only. A season is named by the year
    | it starts in, as football-data.org counts it: 2026 means 2026/27.
    |
    */

    'competition' => 'PL',

    'season' => (int) env('FOOTBALL_SEASON', 2026),

    /*
    |--------------------------------------------------------------------------
    | Display time zone
    |--------------------------------------------------------------------------
    |
    | Kick-off times are stored and compared in UTC, and shown in UK time.
    |
    */

    'display_timezone' => 'Europe/London',

    /*
    |--------------------------------------------------------------------------
    | Cache windows (seconds)
    |--------------------------------------------------------------------------
    |
    | The season is fresh for a minute while a match is live, ten minutes on
    | a matchday and six hours otherwise. After that, a stale copy is still
    | served for up to a day while it refreshes in the background.
    |
    */

    'cache' => [
        'live' => 60,
        'matchday' => 600,
        'idle' => 21_600,
        'stale' => 86_400,
        'competition' => 3_600,
    ],

];
