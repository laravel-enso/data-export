<?php

namespace LaravelEnso\DataExport;

use LaravelEnso\DataExport\Models\DataExport;
use LaravelEnso\Files\FileServiceProvider as ServiceProvider;

class FileServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->register['exports'] = [
            'model' => DataExport::morphMapKey(),
            'order' => 40,
        ];

        parent::boot();
    }
}
