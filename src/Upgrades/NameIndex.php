<?php

namespace LaravelEnso\DataExport\Upgrades;

use Illuminate\Support\Facades\Schema;
use LaravelEnso\Upgrade\Contracts\MigratesTable;
use LaravelEnso\Upgrade\Helpers\Table;

class NameIndex implements MigratesTable
{
    public function isMigrated(): bool
    {
        return Table::hasIndex('data_exports', 'data_exports_name_index');
    }

    public function migrateTable(): void
    {
        Schema::table('data_exports', fn ($table) => $table->index(['name']));
    }
}
