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
use LaravelEnso\Multitenancy\app\Traits\MixedConnection;
use LaravelEnso\Multitenancy\app\Traits\ConnectionStoragePath;

class DataExport extends Model implements Attachable, VisibleFile, IOOperation
{
    use ConnectionStoragePath, CreatedBy, HasIOStatuses, HasFile, MixedConnection;

    protected $fillable = ['name', 'entries', 'status'];

    public function folder()
    {
        return $this->storagePath('exports');
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
