<?php

namespace LaravelEnso\DataExport\app\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Core\app\Enums\IOTypes;
use LaravelEnso\TrackWho\app\Traits\CreatedBy;
use LaravelEnso\Core\app\Contracts\IOOperation;
use LaravelEnso\FileManager\app\Traits\HasFile;
use LaravelEnso\FileManager\app\Contracts\Attachable;
use LaravelEnso\FileManager\app\Contracts\VisibleFile;

class DataExport extends Model implements Attachable, VisibleFile, IOOperation
{
    use CreatedBy, HasFile;

    protected $fillable = ['name', 'entries', 'status'];

    public function folder()
    {
        return config('enso.config.paths.exports');
    }

    public function isDeletable()
    {
        return true;
    }

    public function name()
    {
        return $this->name;
    }

    public function entries()
    {
        return $this->entries;
    }

    public function type()
    {
        return IOTypes::Export;
    }
}
