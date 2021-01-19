<?php

use LaravelEnso\Migrator\Database\Migration;

class CreateStructureForDataExport extends Migration
{
    protected array $permissions = [
        ['name' => 'export.cancel', 'description' => 'Cancel running export', 'is_default' => true],
    ];
}
