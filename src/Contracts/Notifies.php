<?php

namespace LaravelEnso\DataExport\Contracts;

use LaravelEnso\DataExport\Models\DataExport;

interface Notifies
{
    public function notify(DataExport $export): void;
}
