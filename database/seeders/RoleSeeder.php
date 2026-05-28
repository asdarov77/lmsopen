<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['name' => 'Администратор'],
            ['description' => null]
        );

        Role::updateOrCreate(
            ['name' => 'Инструктор'],
            ['description' => null]
        );

        Role::updateOrCreate(
            ['name' => 'Обучаемый'],
            ['description' => null]
        );
    }
}
