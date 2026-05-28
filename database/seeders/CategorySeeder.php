<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'aircraft_id' => 1,
                'title' => 'Летчик',
                'code' => 'PILOT',
                'description' => 'курсы для летчика',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Борт инженер',
                'code' => 'FLIGHT_ENG',
                'description' => 'курсы для борт инженера',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Инженер АВ',
                'code' => 'AV_ENG',
                'description' => 'курсы для инженера АВ',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Инженер АСУ',
                'code' => 'ASU_ENG',
                'description' => 'курсы для инженера АСУ',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Штурман',
                'code' => 'NAV',
                'description' => 'курсы для штурмана',
            ],
        ]);
    }
}
