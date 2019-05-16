<?php

namespace LaravelEnso\DataExport\app\Models;

use LaravelEnso\IO\app\Enums\IOTypes;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Files\app\Traits\HasFile;
use LaravelEnso\IO\app\Traits\HasIOStatuses;
use LaravelEnso\IO\app\Contracts\IOOperation;
use LaravelEnso\TrackWho\app\Traits\CreatedBy;
use LaravelEnso\Files\app\Contracts\Attachable;
use LaravelEnso\Files\app\Contracts\VisibleFile;

class DataExport extends Model implements Attachable, VisibleFile, IOOperation
{
    use CreatedBy, HasIOStatuses, HasFile;

    protected $fillable = ['name', 'entries', 'status', 'created_by'];

    protected $folder = 'exports';

    public function isDeletable(): bool
    {
        return true;
    }

    public function type()
    {
        return IOTypes::Export;
    }
}
