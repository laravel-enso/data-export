<?php

namespace LaravelEnso\DataExport\Contracts;

use LaravelEnso\DataExport\Models\Export;

interface Notifies
{
    public function notify(Export $export): void;
}
