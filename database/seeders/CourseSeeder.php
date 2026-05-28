<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('courses')->insert([
            [
                'aircraft_id' => 1,
                'title' => 'Подготовка и выполнение полета. Боевое применение',
                'short_description' => 'курсы для летчика',
                'long_description' => '',
                'path' => 'course-1',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Эксплуатация оборудования',
                'short_description' => 'курсы для борт инженера',
                'long_description' => '',
                'path' => 'course-2',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Авиационное вооружение и десантно-транспортное оборудование',
                'short_description' => 'курсы для инженера АВ',
                'long_description' => '',
                'path' => 'course-3',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Подготовка к полетам самолета, самолетных систем и силовой установки',
                'short_description' => 'курсы для бортового техника',
                'long_description' => '',
                'path' => 'course-4',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Авиационное оборудование',
                'short_description' => 'курсы для АО',
                'long_description' => '',
                'path' => 'course-5',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Планер самолета',
                'short_description' => 'курсы планера самолета',
                'long_description' => '',
                'path' => 'course-6',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Силовая установка',
                'short_description' => 'курсы двигатель',
                'long_description' => '',
                'path' => 'course-7',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Силовая установка',
                'short_description' => 'курсы двигатель',
                'long_description' => '',
                'path' => 'course-8',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Конструкция вертолета и его системы. Описание и эксплуатация',
                'short_description' => 'описание вертолета..',
                'long_description' => '',
                'path' => 'course-9',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Практическая аэродинамика',
                'short_description' => 'курсы аэродинамика..',
                'long_description' => '',
                'path' => 'course-10',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Летная эксплуатация (раздел 4 Летные данные)',
                'short_description' => 'курсы ЛЭ',
                'long_description' => '',
                'path' => 'course-11',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Конструкция вертолета и его системы. Техническое обслуживание',
                'short_description' => 'курсы ТО',
                'long_description' => '',
                'path' => 'course-12',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Конструкция силовой установки. Техническое обслуживание',
                'short_description' => 'курсы ТО',
                'long_description' => '',
                'path' => 'course-13',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Радиоэлектронное оборудование. Техническое обслуживание',
                'short_description' => 'курсы РЭО',
                'long_description' => '',
                'path' => 'course-14',
            ],
            [
                'aircraft_id' => 1,
                'title' => 'Аварийно-спасательные средства. Техническое описание',
                'short_description' => 'курсы РЭО',
                'long_description' => '',
                'path' => 'course-15',
            ],
        ]);
    }
}
