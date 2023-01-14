<?php

namespace App\Providers;

use App\Models\File;
use App\Models\User;
use App\Policies\FilePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot() : void
    {
        $this->registerPolicies();

        Gate::define(FilePolicy::DOWNLOAD, [FilePolicy::class, 'download']);
        Gate::define(FilePolicy::DELETE, [FilePolicy::class, 'delete']);
        Gate::define(FilePolicy::PATCH, [FilePolicy::class, 'patch']);
    }
}
