<?php

namespace LaravelEnso\DataExport\App\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Files\App\Contracts\Attachable;
use LaravelEnso\Files\App\Contracts\AuthorizesFileAccess;
use LaravelEnso\Files\App\Traits\FilePolicies;
use LaravelEnso\Files\App\Traits\HasFile;
use LaravelEnso\Helpers\App\Traits\CascadesMorphMap;
use LaravelEnso\IO\App\Contracts\IOOperation;
use LaravelEnso\IO\App\Enums\IOTypes;
use LaravelEnso\IO\App\Traits\HasIOStatuses;
use LaravelEnso\TrackWho\App\Traits\CreatedBy;

class DataExport extends Model implements Attachable, IOOperation, AuthorizesFileAccess
{
    use CascadesMorphMap, CreatedBy, HasIOStatuses, HasFile, FilePolicies;

    protected $fillable = ['name', 'entries', 'status', 'created_by'];

    protected $folder = 'exports';

    public function type()
    {
        return IOTypes::Export;
    }
}
