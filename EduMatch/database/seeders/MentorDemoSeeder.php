<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MentorDemoSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'MentorDemo2026!';

    public function run(): void
    {
        $this->call(SubjectSeeder::class);

        $mentors = [
            [
                'name' => 'Laura Méndez',
                'email' => 'laura.mendez@edumatch.test',
                'career' => 'Ingeniería en Sistemas',
                'academic_year' => 5,
                'bio' => 'Perfil de demostración. Lleva dos años apoyando a estudiantes con álgebra y cálculo. Le gusta explicar paso a paso y usar ejemplos de situaciones cotidianas.',
                'subjects' => ['Álgebra', 'Cálculo', 'Matemática'],
                'availabilities' => [
                    [1, '15:00', '18:00'],
                    [3, '14:00', '17:00'],
                ],
            ],
            [
                'name' => 'Carlos Ramírez',
                'email' => 'carlos.ramirez@edumatch.test',
                'career' => 'Ingeniería Eléctrica',
                'academic_year' => 5,
                'bio' => 'Perfil de demostración. Ha acompañado a compañeros en física y matemática durante tres ciclos. Prefiere resolver problemas juntos y explicar el razonamiento detrás de cada fórmula.',
                'subjects' => ['Física', 'Matemática', 'Cálculo'],
                'availabilities' => [
                    [2, '16:00', '19:00'],
                    [4, '13:00', '16:00'],
                ],
            ],
            [
                'name' => 'Mariana Flores',
                'email' => 'mariana.flores@edumatch.test',
                'career' => 'Ingeniería Química',
                'academic_year' => 4,
                'bio' => 'Perfil de demostración. Comparte guías de estudio y estrategias para entender química y álgebra. Su experiencia como mentora se centra en reforzar conceptos antes de los exámenes.',
                'subjects' => ['Química', 'Álgebra'],
                'availabilities' => [
                    [1, '09:00', '12:00'],
                    [5, '14:00', '17:00'],
                ],
            ],
            [
                'name' => 'Diego Hernández',
                'email' => 'diego.hernandez@edumatch.test',
                'career' => 'Ingeniería en Sistemas',
                'academic_year' => 5,
                'bio' => 'Perfil de demostración. Tiene experiencia apoyando prácticas de programación y estructuras de datos. Trabaja con ejercicios pequeños para fortalecer la lógica y la confianza al programar.',
                'subjects' => ['Programación', 'Estructuras de datos', 'Bases de datos'],
                'availabilities' => [
                    [2, '14:00', '18:00'],
                    [4, '15:00', '18:00'],
                ],
            ],
            [
                'name' => 'Sofía Castillo',
                'email' => 'sofia.castillo@edumatch.test',
                'career' => 'Licenciatura en Economía y Negocios',
                'academic_year' => 4,
                'bio' => 'Perfil de demostración. Apoya a estudiantes con estadística, economía y contabilidad. Le gusta relacionar los ejercicios con ejemplos sencillos y organizar el material en pasos claros.',
                'subjects' => ['Estadística', 'Economía', 'Contabilidad'],
                'availabilities' => [
                    [3, '10:00', '13:00'],
                    [5, '09:00', '12:00'],
                ],
            ],
            [
                'name' => 'Andrés Molina',
                'email' => 'andres.molina@edumatch.test',
                'career' => 'Licenciatura en Idiomas',
                'academic_year' => 4,
                'bio' => 'Perfil de demostración. Practica inglés conversacional y apoya con comprensión y redacción. Sus sesiones se enfocan en ganar fluidez y aprender a expresarse sin temor a equivocarse.',
                'subjects' => ['Inglés', 'Redacción académica'],
                'availabilities' => [
                    [1, '13:00', '16:00'],
                    [6, '09:00', '12:00'],
                ],
            ],
        ];

        Subject::firstOrCreate(['name' => 'Redacción académica']);

        foreach ($mentors as $details) {
            $subjectNames = $details['subjects'];
            $availabilitySlots = $details['availabilities'];
            unset($details['subjects'], $details['availabilities']);

            $mentor = User::updateOrCreate(
                ['email' => $details['email']],
                [
                    ...$details,
                    'role' => 'mentor',
                    'password' => Hash::make(self::DEMO_PASSWORD),
                ]
            );

            $subjectIds = Subject::query()
                ->whereIn('name', $subjectNames)
                ->pluck('id')
                ->all();

            $mentor->subjects()->sync(
                array_fill_keys($subjectIds, ['kind' => 'mentor'])
            );

            $mentor->availabilities()->delete();
            $mentor->availabilities()->createMany(array_map(
                fn (array $slot) => [
                    'day_of_week' => $slot[0],
                    'starts_at' => $slot[1],
                    'ends_at' => $slot[2],
                ],
                $availabilitySlots
            ));
        }
    }
}
