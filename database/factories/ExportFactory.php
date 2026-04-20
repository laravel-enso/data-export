<?php

namespace LaravelEnso\DataExport\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelEnso\DataExport\Enums\Status;
use LaravelEnso\DataExport\Models\Export;

class ExportFactory extends Factory
{
    protected $model = Export::class;

    public function definition()
    {
        return [
            'name' => null,
            'entries' => 0,
            'total' => 0,
            'status' => Status::Waiting->value,
        ];
    }
}
