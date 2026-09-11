<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | TinyPHP key
    |--------------------------------------------------------------------------
    |
    | Key that the Tiny class uses.
    |
    */
    'key' => env('TINY_KEY', env('LEAGUE_TINY_KEY', '')),
];