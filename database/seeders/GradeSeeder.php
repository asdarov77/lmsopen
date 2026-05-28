<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GradeBoundary;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $gradeBoundaries = [
            ['boundary' => 0, 'grade' => 2],
            ['boundary' => 35, 'grade' => 3],
            ['boundary' => 65, 'grade' => 4],
            ['boundary' => 85, 'grade' => 5],
        ];

        foreach ($gradeBoundaries as $gradeBoundary) {
            GradeBoundary::updateOrCreate(
                ['boundary' => $gradeBoundary['boundary']],
                $gradeBoundary
            );
        }
    }
}
