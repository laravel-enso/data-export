<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Row limit per sheet
    |--------------------------------------------------------------------------
    |
    | Sets the default row limit per sheet used for excel exports.
    |
     */

    'rowLimit' => env('EXPORT_ROW_LIMIT', 1000000),

    /*
    |--------------------------------------------------------------------------
    | Retain exports for a number of days
    |--------------------------------------------------------------------------
    | The default period in days for retaining exports. Is used by the
    | Purge command.
    |
     */

    'retainFor' => (int) env('EXPORT_RETAIN_FOR', 60),
];
