<?php

namespace LaravelEnso\DataExport\app\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Files\app\Contracts\Attachable;
use LaravelEnso\Files\app\Contracts\AuthorizesFileAccess;
use LaravelEnso\Files\app\Traits\FilePolicies;
use LaravelEnso\Files\app\Traits\HasFile;
use LaravelEnso\IO\app\Contracts\IOOperation;
use LaravelEnso\IO\app\Enums\IOTypes;
use LaravelEnso\IO\app\Traits\HasIOStatuses;
use LaravelEnso\TrackWho\app\Traits\CreatedBy;

class DataExport extends Model implements Attachable, IOOperation, AuthorizesFileAccess
{
    use CreatedBy, HasIOStatuses, HasFile, FilePolicies;

    protected $fillable = ['name', 'entries', 'status', 'created_by'];

    protected $folder = 'exports';

    public function type()
    {
        return IOTypes::Export;
    }
}
