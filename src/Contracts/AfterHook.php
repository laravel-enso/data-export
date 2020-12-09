<?php

namespace LaravelEnso\DataExport\Contracts;

use LaravelEnso\DataExport\Models\DataExport;

interface AfterHook
{
    public function after(DataExport $export): void;
}
