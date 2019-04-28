<?php

namespace LaravelEnso\DataExport\app\Contracts;

interface BeforeExportHook
{
    public function before(): void;
}
