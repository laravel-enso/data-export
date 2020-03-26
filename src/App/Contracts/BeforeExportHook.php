<?php

namespace LaravelEnso\DataExport\App\Contracts;

interface BeforeExportHook
{
    public function before(): void;
}
