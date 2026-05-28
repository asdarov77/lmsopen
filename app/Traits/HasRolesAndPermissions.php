<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;

trait HasRolesAndPermissions
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permissions_users');
    }

    public function hasRole(...$roles)
    {
        foreach ($roles as $role) {
            if ($this->roles->contains('name', $role)) {
                return true;
            }
        }
        return false;
    }

    public function allPermissions()
    {
        $directPermissions = $this->permissions;
        $rolePermissions = collect();
        foreach ($this->roles as $role) {
            $rolePermissions = $rolePermissions->merge($role->permissions);
        }
        return $directPermissions->merge($rolePermissions)->unique('id');
    }

    public function hasPermission($permission)
    {
        return (bool) $this->allPermissions()->where('description', $permission)->count();
    }

    public function getAllPermissions(array $permissions)
    {
        return Permission::whereIn('name', $permissions)->get();
    }

    public function givePermissionsTo(...$permissions)
    {
        $permissions = $this->getAllPermissions($permissions);
        if ($permissions === null) {
            return $this;
        }
        $this->permissions()->saveMany($permissions);
        return $this;
    }

    public function deletePermissions(...$permissions)
    {
        $permissions = $this->getAllPermissions($permissions);
        $this->permissions()->detach($permissions);
        return $this;
    }

    public function refreshPermissions(...$permissions)
    {
        $this->permissions()->detach();
        return $this->givePermissionsTo($permissions);
    }

    public function hasPerm($permission)
    {
        return (bool) $this->allPermissions()->where('description', $permission)->count();
    }
}
