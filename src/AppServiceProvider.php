<?php

namespace LaravelEnso\DataExport;

use Illuminate\Support\ServiceProvider;
use LaravelEnso\DataExport\Commands\Purge;
use LaravelEnso\DataExport\Models\DataExport;
use LaravelEnso\IO\Observers\IOObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->load()
            ->publish()
            ->commands(Purge::class);

        DataExport::morphMap();
        DataExport::observe(IOObserver::class);
    }

    private function load()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        $this->mergeConfigFrom(__DIR__.'/../config/exports.php', 'enso.exports');

        return $this;
    }

    private function publish()
    {
        $this->publishes([
            __DIR__.'/../config' => config_path('enso'),
        ], ['data-export-config', 'enso-config']);

        return $this;
    }
}
