<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AircraftSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            GroupSeeder::class,
            UserSeeder::class,
            PermissionAssignmentSeeder::class,
            GradeSeeder::class,
            CategorySeeder::class,
            CourseSeeder::class,
        ]);
    }
}
