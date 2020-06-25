<?php

namespace LaravelEnso\DataExport\Contracts;

interface AfterExportHook
{
    public function after(): void;
}
