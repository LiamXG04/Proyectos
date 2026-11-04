<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Álgebra',
            'Cálculo',
            'Física',
            'Química',
            'Programación',
            'Estructuras de datos',
            'Inglés',
            'Estadística',
            'Economía',
            'Contabilidad',
            'Matemática',
            'Bases de datos',
        ] as $name) {
            Subject::firstOrCreate(['name' => $name]);
        }
    }
}
