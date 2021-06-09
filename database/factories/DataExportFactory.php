<?php

namespace LaravelEnso\DataExport\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelEnso\DataExport\Enums\Statuses;
use LaravelEnso\DataExport\Models\DataExport;

class DataExportFactory extends Factory
{
    protected $model = DataExport::class;

    public function definition()
    {
        return [
            'name' => null,
            'entries' => 0,
            'total' => 0,
            'status' => Statuses::Waiting,
        ];
    }
}
