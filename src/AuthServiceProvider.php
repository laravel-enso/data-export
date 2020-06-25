<?php

namespace LaravelEnso\DataExport;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use LaravelEnso\DataExport\Models\DataExport;
use LaravelEnso\DataExport\Policies\Policy;

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
