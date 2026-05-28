<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::updateOrCreate(
            ['name' => 'управление пользователями'],
            ['description' => 'manage-users']
        );

        Permission::updateOrCreate(
            ['name' => 'Create Tasks'],
            ['description' => 'create-tasks']
        );

        Permission::updateOrCreate(
            ['name' => 'управление курсами'],
            ['description' => 'manage-course']
        );
    }
}
