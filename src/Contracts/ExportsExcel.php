<?php

namespace LaravelEnso\DataExport\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface ExportsExcel
{
    public function filename(): string;

    public function heading(): array;

    public function query(): Builder;

    public function attributes(): array;

    public function mapping($row): array;
}
