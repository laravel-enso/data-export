<?php

namespace LaravelEnso\DataExport;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use LaravelEnso\DataExport\App\Models\DataExport;
use LaravelEnso\DataExport\App\Policies\Policy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        DataExport::class => Policy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
