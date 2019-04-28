<?php

namespace LaravelEnso\DataExport\app\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface ExportsExcel
{
    public function heading(): array;

    public function query(): Builder;

    public function attributes(): array;

    public function mapping($row): array;
}
