<?php

namespace LaravelEnso\DataExport\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Files\Contracts\Attachable;
use LaravelEnso\Files\Contracts\AuthorizesFileAccess;
use LaravelEnso\Files\Traits\FilePolicies;
use LaravelEnso\Files\Traits\HasFile;
use LaravelEnso\Helpers\Traits\CascadesMorphMap;
use LaravelEnso\IO\Contracts\IOOperation;
use LaravelEnso\IO\Enums\IOTypes;
use LaravelEnso\IO\Traits\HasIOStatuses;
use LaravelEnso\TrackWho\Traits\CreatedBy;

class DataExport extends Model implements Attachable, IOOperation, AuthorizesFileAccess
{
    use CascadesMorphMap, CreatedBy, HasIOStatuses, HasFile, FilePolicies;

    protected $guarded = ['id'];

    protected $folder = 'exports';

    public function type()
    {
        return IOTypes::Export;
    }
}
