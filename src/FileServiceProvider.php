<?php

namespace LaravelEnso\DataExport;

use LaravelEnso\DataExport\app\Models\DataExport;
use LaravelEnso\Files\FileServiceProvider as ServiceProvider;

class FileServiceProvider extends ServiceProvider
{
    public $register = [
        'exports' => [
            'model' => DataExport::class,
            'order' => 40,
        ],
    ];
}
