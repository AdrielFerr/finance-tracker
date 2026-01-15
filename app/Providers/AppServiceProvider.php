<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Policies\TenantPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Tenant::class => TenantPolicy::class,
         \App\Models\Lead::class => \App\Policies\LeadPolicy::class,
        \App\Models\LeadPipeline::class => \App\Policies\LeadPipelinePolicy::class,
        \App\Models\LeadActivity::class => \App\Policies\LeadActivityPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
