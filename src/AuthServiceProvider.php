<?php

namespace LaravelEnso\DataExport;

use LaravelEnso\DataExport\app\Policies\Policy;
use LaravelEnso\DataExport\app\Models\DataExport;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
