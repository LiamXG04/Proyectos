<?php

namespace Database\Seeders;

use App\Models\MentoringSession;
use App\Models\Review;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class MentorDemoReviewSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'MentorDemo2026!';

    public function run(): void
    {
        $this->call(MentorDemoSeeder::class);

        $reviews = [
            [
                'mentor' => 'laura.mendez@edumatch.test',
                'subject' => 'Cálculo',
                'reviews' => [
                    ['name' => 'Valeria Rivas', 'email' => 'valeria.rivas@edumatch.test', 'rating' => 5, 'comment' => 'Me explicó los límites con ejemplos muy claros. Por fin pude resolver los ejercicios por mi cuenta.'],
                    ['name' => 'Mateo Aguilar', 'email' => 'mateo.aguilar@edumatch.test', 'rating' => 5, 'comment' => 'Fue muy paciente y ordenada. Repasamos derivadas paso a paso y salí con más confianza para el examen.'],
                ],
            ],
            [
                'mentor' => 'carlos.ramirez@edumatch.test',
                'subject' => 'Física',
                'reviews' => [
                    ['name' => 'Daniela Cruz', 'email' => 'daniela.cruz@edumatch.test', 'rating' => 5, 'comment' => 'Me ayudó a entender qué fórmula usar y, sobre todo, por qué. Practicamos varios problemas sin apuro.'],
                    ['name' => 'Gabriel Torres', 'email' => 'gabriel.torres@edumatch.test', 'rating' => 4, 'comment' => 'Buena explicación de cinemática y ejercicios útiles. Llegué con varias dudas y logramos aclarar la mayoría.'],
                ],
            ],
            [
                'mentor' => 'mariana.flores@edumatch.test',
                'subject' => 'Química',
                'reviews' => [
                    ['name' => 'Camila Reyes', 'email' => 'camila.reyes@edumatch.test', 'rating' => 5, 'comment' => 'Los ejemplos para balancear ecuaciones me sirvieron mucho. Me gustó que comprobáramos cada paso juntas.'],
                    ['name' => 'José Pineda', 'email' => 'jose.pineda@edumatch.test', 'rating' => 5, 'comment' => 'Excelente apoyo con estequiometría. Me compartió una forma ordenada de revisar los resultados.'],
                ],
            ],
            [
                'mentor' => 'diego.hernandez@edumatch.test',
                'subject' => 'Programación',
                'reviews' => [
                    ['name' => 'Fernanda López', 'email' => 'fernanda.lopez@edumatch.test', 'rating' => 5, 'comment' => 'Revisamos mi lógica con ejercicios pequeños. Diego explicó los errores con mucha claridad y sin hacer el trabajo por mí.'],
                    ['name' => 'Ricardo Mejía', 'email' => 'ricardo.mejia@edumatch.test', 'rating' => 4, 'comment' => 'Me ayudó a organizar mejor mis consultas de bases de datos. La sesión fue práctica y fácil de seguir.'],
                ],
            ],
            [
                'mentor' => 'sofia.castillo@edumatch.test',
                'subject' => 'Estadística',
                'reviews' => [
                    ['name' => 'Paola Díaz', 'email' => 'paola.diaz@edumatch.test', 'rating' => 5, 'comment' => 'Me explicó cómo elegir la prueba estadística con ejemplos. Ahora entiendo mejor cómo interpretar los resultados.'],
                    ['name' => 'Samuel Campos', 'email' => 'samuel.campos@edumatch.test', 'rating' => 5, 'comment' => 'Muy buena sesión de probabilidad. Sofía hizo preguntas para asegurarse de que entendiera cada concepto.'],
                ],
            ],
            [
                'mentor' => 'andres.molina@edumatch.test',
                'subject' => 'Inglés',
                'reviews' => [
                    ['name' => 'Lucía Herrera', 'email' => 'lucia.herrera@edumatch.test', 'rating' => 5, 'comment' => 'La práctica de conversación fue muy amena. Me dio correcciones claras y pude hablar con más seguridad.'],
                    ['name' => 'Emilio Navarro', 'email' => 'emilio.navarro@edumatch.test', 'rating' => 4, 'comment' => 'Repasamos comprensión y pronunciación con paciencia. Me gustó que practicáramos situaciones cotidianas.'],
                ],
            ],
        ];

        foreach ($reviews as $mentorReviews) {
            $mentor = User::where('email', $mentorReviews['mentor'])->firstOrFail();
            $subject = Subject::where('name', $mentorReviews['subject'])->firstOrFail();

            foreach ($mentorReviews['reviews'] as $index => $reviewDetails) {
                $mentee = User::updateOrCreate(
                    ['email' => $reviewDetails['email']],
                    [
                        'name' => $reviewDetails['name'],
                        'role' => 'mentee',
                        'career' => 'Estudiante universitario',
                        'academic_year' => 2,
                        'bio' => 'Cuenta ficticia utilizada únicamente para reseñas de demostración.',
                        'password' => Hash::make(self::DEMO_PASSWORD),
                    ]
                );

                $session = MentoringSession::firstOrCreate(
                    [
                        'mentor_id' => $mentor->id,
                        'mentee_id' => $mentee->id,
                        'subject_id' => $subject->id,
                        'note' => 'Sesión completada de demostración sobre '.$subject->name.'.',
                    ],
                    [
                        'starts_at' => Carbon::now()->subDays(18 + ($index * 7)),
                        'duration' => 45,
                        'status' => 'completed',
                    ]
                );

                Review::updateOrCreate(
                    ['mentoring_session_id' => $session->id],
                    [
                        'mentor_id' => $mentor->id,
                        'mentee_id' => $mentee->id,
                        'rating' => $reviewDetails['rating'],
                        'comment' => $reviewDetails['comment'],
                    ]
                );
            }
        }
    }
}
