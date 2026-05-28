<?php

namespace App\Providers;

use App\Models\Group;
use App\Models\Permission;
use App\Policies\GroupPolicy;
use App\Policies\PermissionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Group::class => GroupPolicy::class,
        Permission::class => PermissionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('view-group', function ($user) {
            return $user->hasPermission('manage-users');
        });

        Gate::define('delete-group', function ($user) {
            return $user->hasPermission('manage-users');
        });

        Gate::define('manage-users', function ($user) {
            return $user->hasPermission('manage-users');
        });

        Gate::define('manage-course', function ($user) {
            return $user->hasPermission('manage-course');
        });
    }
}
