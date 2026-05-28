<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AircraftSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('aircrafts')->insert([
            [
                'path' => 'Ка-52',
                'title' => 'title Ка-52',
            ],
            [
                'path' => 'Ми-28',
                'title' => 'title Ми-28',
            ],
        ]);
    }
}
