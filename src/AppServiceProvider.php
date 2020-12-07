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
            ->command()
            ->morphMap()
            ->observe();
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

    private function command(): self
    {
        $this->commands(Purge::class);

        $this->app->booted(fn () => $this->app->make(Schedule::class)
            ->command('enso:data-export:purge')->daily());

        return $this;
    }

    private function morphMap(): self
    {
        DataExport::morphMap();

        return $this;
    }

    private function observe(): void
    {
        DataExport::observe(IOObserver::class);
    }
}
