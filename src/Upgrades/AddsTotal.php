<?php

namespace LaravelEnso\DataExport\Upgrades;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LaravelEnso\DataExport\Models\DataExport;
use LaravelEnso\Upgrade\Contracts\MigratesData;
use LaravelEnso\Upgrade\Contracts\MigratesPostDataMigration;
use LaravelEnso\Upgrade\Contracts\MigratesTable;
use LaravelEnso\Upgrade\Helpers\Table;

class AddsTotal implements MigratesTable, MigratesData, MigratesPostDataMigration
{
    public function isMigrated(): bool
    {
        return Table::hasColumn('data_exports', 'total');
    }

    public function migrateTable(): void
    {
        Schema::table('data_exports', fn ($table) => $table
            ->integer('total')->after('entries')->nullable());
    }

    public function migrateData(): void
    {
        DataExport::query()->update(['total' => DB::raw('entries')]);
    }

    public function migratePostDataMigration(): void
    {
        Schema::table('data_exports', fn ($table) => $table
            ->integer('total')->nullable(false)->change());
    }
}
