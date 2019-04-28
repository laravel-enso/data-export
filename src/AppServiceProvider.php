<?php

namespace LaravelEnso\DataExport;

use Illuminate\Support\ServiceProvider;
use LaravelEnso\IO\app\Observers\IOObserver;
use LaravelEnso\DataExport\app\Models\DataExport;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        DataExport::observe(IOObserver::class);

        $this->loadDependencies()
            ->publishDependencies();
    }

    private function loadDependencies()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->mergeConfigFrom(__DIR__.'/config/exports.php', 'exports');

        return $this;
    }

    private function publishDependencies()
    {
        $this->publishes([
            __DIR__.'/config' => config_path('enso'),
        ], 'dataexport-config');

        $this->publishes([
            __DIR__.'/config' => config_path('enso'),
        ], 'enso-config');
    }
}
