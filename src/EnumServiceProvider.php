<?php

namespace LaravelEnso\DataExport;

use LaravelEnso\DataExport\Enums\Statuses;
use LaravelEnso\Enums\EnumServiceProvider as ServiceProvider;

class EnumServiceProvider extends ServiceProvider
{
    public $register = [
        'exportStatuses' => Statuses::class,
    ];
}
