<?php

namespace LaravelEnso\DataExport\Upgrades;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Permissions\Models\Permission;
use LaravelEnso\Upgrade\Contracts\MigratesData;

class UpdatePermission implements MigratesData
{
    private const Update = [
        'oldDescription' => 'Export import',
        'newDescription' => 'Cancel running export',
    ];

    public function isMigrated(): bool
    {
        return $this->query()->doesntExist();
    }

    public function migrateData(): void
    {
        $this->query()
            ->update(['description' => self::Update['newDescription']]);
    }

    private function query(): Builder
    {
        return Permission::whereDescription(self::Update['oldDescription']);
    }
}
