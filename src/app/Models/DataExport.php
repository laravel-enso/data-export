<?php

namespace LaravelEnso\DataExport\app\Models;

use LaravelEnso\IO\app\Enums\IOTypes;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\IO\app\Traits\HasIOStatuses;
use LaravelEnso\IO\app\Contracts\IOOperation;
use LaravelEnso\TrackWho\app\Traits\CreatedBy;
use LaravelEnso\FileManager\app\Traits\HasFile;
use LaravelEnso\FileManager\app\Contracts\Attachable;
use LaravelEnso\FileManager\app\Contracts\VisibleFile;
use LaravelEnso\Multitenancy\app\Traits\SystemConnection;

class DataExport extends Model implements Attachable, VisibleFile, IOOperation
{
    use CreatedBy, HasIOStatuses, HasFile, SystemConnection;

    protected $fillable = ['name', 'entries', 'status'];

    public function folder()
    {
        return config('enso.config.paths.exports');
    }

    public function isDeletable()
    {
        return true;
    }

    public function type()
    {
        return IOTypes::Export;
    }
}
