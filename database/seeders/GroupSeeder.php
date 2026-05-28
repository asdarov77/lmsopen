<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        Group::create([
            'groupname' => 'Группа 1',
            'groupdescription' => 'Первая группа',
        ]);

        Group::create([
            'groupname' => 'Группа 2',
            'groupdescription' => 'Вторая группа',
        ]);

        Group::create([
            'groupname' => 'Группа 3',
            'groupdescription' => 'Третья группа',
        ]);
    }
}
