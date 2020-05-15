<?php

namespace LaravelEnso\DataExport;

use LaravelEnso\DataExport\App\Models\DataExport;
use LaravelEnso\Files\FileServiceProvider as ServiceProvider;

class FileServiceProvider extends ServiceProvider
{
    public $register = [
        'exports' => [
            'model' => 'dataExport', //TODO DataExport::morphMapKey()
            'order' => 40,
        ],
    ];
}
