<?php

namespace LaravelEnso\DataExport;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use LaravelEnso\DataExport\Models\Export;
use LaravelEnso\DataExport\Policies\Policy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Export::class => Policy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
