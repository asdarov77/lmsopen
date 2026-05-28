<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Permission $permission)
    {
        return $user->hasPermission('manage-users');
    }
}
