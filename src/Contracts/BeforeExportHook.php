<?php

namespace LaravelEnso\DataExport\Contracts;

interface BeforeExportHook
{
    public function before(): void;
}
