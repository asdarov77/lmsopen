<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('permissions_roles')->insert([
            ['role_id' => 1, 'permission_id' => 1],
            ['role_id' => 1, 'permission_id' => 2],
            ['role_id' => 1, 'permission_id' => 3],
        ]);

        DB::table('role_user')->insert([
            ['role_id' => 1, 'user_id' => 1],
        ]);

        DB::table('permissions_users')->insert([
            ['permission_id' => 1, 'user_id' => 1],
            ['permission_id' => 2, 'user_id' => 1],
            ['permission_id' => 3, 'user_id' => 1],
        ]);

        DB::table('role_user')->insert([
            ['role_id' => 2, 'user_id' => 2],
        ]);

        DB::table('permissions_users')->insert([
            ['permission_id' => 2, 'user_id' => 2],
            ['permission_id' => 3, 'user_id' => 2],
        ]);
    }
}
