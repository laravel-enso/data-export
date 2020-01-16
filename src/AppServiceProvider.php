<?php

namespace LaravelEnso\DataExport;

use Illuminate\Support\ServiceProvider;
use LaravelEnso\DataExport\App\Commands\Purge;
use LaravelEnso\DataExport\App\Models\DataExport;
use LaravelEnso\IO\App\Observers\IOObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        DataExport::observe(IOObserver::class);

        $this->load()
            ->publish()
            ->commands(Purge::class);
    }

    private function load()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->mergeConfigFrom(__DIR__.'/config/exports.php', 'enso.exports');

        return $this;
    }

    private function publish()
    {
        $this->publishes([
            __DIR__.'/config' => config_path('enso'),
        ], ['data-export-config', 'enso-config']);

        return $this;
    }
}
