<?php

namespace LaravelEnso\DataExport\app\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelEnso\TrackWho\app\Traits\CreatedBy;
use LaravelEnso\FileManager\app\Traits\HasFile;
use LaravelEnso\FileManager\app\Contracts\Attachable;
use LaravelEnso\FileManager\app\Contracts\VisibleFile;

class DataExport extends Model implements Attachable, VisibleFile
{
    use HasFile, CreatedBy;

    public function folder()
    {
        return config('enso.config.paths.exports');
    }

    public function isDeletable()
    {
        return true;
    }
}
