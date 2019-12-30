<?php

namespace LaravelEnso\DataExport\App\Contracts;

interface AfterExportHook
{
    public function after(): void;
}
