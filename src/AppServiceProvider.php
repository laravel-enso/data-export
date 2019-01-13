<?php

namespace LaravelEnso\DataExport;

use Illuminate\Support\ServiceProvider;
use LaravelEnso\Core\app\Observers\IOObserver;
use LaravelEnso\DataExport\app\Models\DataExport;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        DataExport::observe(IOObserver::class);

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    public function register()
    {
        //
    }
}
