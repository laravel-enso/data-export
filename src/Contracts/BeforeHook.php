<?php

namespace LaravelEnso\DataExport\Contracts;

use LaravelEnso\DataExport\Models\Export;

interface BeforeHook
{
    public function before(Export $export): void;
}
