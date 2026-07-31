<?php

namespace LaravelEnso\DataExport\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use LaravelEnso\DataExport\Enums\Statuses;
use LaravelEnso\DataExport\Models\Export;

class ExportFactory extends Factory
{
    protected $model = Export::class;

    public function definition()
    {
        return [
            'name'    => Str::uuid().'.xlsx',
            'entries' => 0,
            'total'   => 0,
            'status'  => Statuses::Waiting,
        ];
    }
}
