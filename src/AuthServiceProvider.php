<?php

namespace LaravelEnso\DataExport;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use LaravelEnso\DataExport\app\Models\DataExport;
use LaravelEnso\DataExport\app\Policies\Policy;

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
