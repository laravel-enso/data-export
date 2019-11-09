<?php

namespace LaravelEnso\DataExport;

use Illuminate\Support\ServiceProvider;
use LaravelEnso\DataExport\app\Models\DataExport;
use LaravelEnso\IO\app\Observers\IOObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        DataExport::observe(IOObserver::class);

        $this->load()
            ->publish();
    }

    private function load()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->mergeConfigFrom(__DIR__.'/config/exports.php', 'exports');

        return $this;
    }

    private function publish()
    {
        $this->publishes([
            __DIR__.'/config' => config_path('enso'),
        ], 'data-export-config');

        $this->publishes([
            __DIR__.'/config' => config_path('enso'),
        ], 'enso-config');
    }
}
