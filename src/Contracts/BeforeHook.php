<?php

namespace LaravelEnso\DataExport\Contracts;

use LaravelEnso\DataExport\Models\DataExport;

interface BeforeHook
{
    public function before(DataExport $export): void;
}
