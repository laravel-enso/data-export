<?php

use LaravelEnso\Migrator\Database\Migration;

class CreateStructureForDataExport extends Migration
{
    protected array $permissions = [
        ['name' => 'export.cancel', 'description' => 'Export import', 'is_default' => true],
    ];
}
