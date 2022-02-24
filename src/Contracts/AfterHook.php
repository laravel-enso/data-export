<?php

namespace LaravelEnso\DataExport\Contracts;

use LaravelEnso\DataExport\Models\Export;

interface AfterHook
{
    public function after(Export $export): void;
}
