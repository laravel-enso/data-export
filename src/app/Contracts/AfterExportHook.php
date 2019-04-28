<?php

namespace LaravelEnso\DataExport\app\Contracts;

interface AfterExportHook
{
    public function after(): void;
}
