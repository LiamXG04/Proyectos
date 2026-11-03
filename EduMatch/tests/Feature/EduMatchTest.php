<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\User;
use Database\Seeders\MentorDemoReviewSeeder;
use Database\Seeders\MentorDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EduMatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_can_register_with_a_role_and_academic_profile(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Andrea Estudiante',
            'email' => 'andrea@example.edu',
            'role' => 'mentee',
            'career' => 'Ingeniería en Sistemas',
            'academic_year' => 2,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'andrea@example.edu',
            'role' => 'mentee',
            'career' => 'Ingeniería en Sistemas',
            'academic_year' => 2,
        ]);
    }

    public function test_authenticated_students_are_redirected_to_the_dashboard_from_guest_pages(): void
    {
        $student = User::factory()->create(['role' => 'mentee']);

        $this->actingAs($student)
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_a_mentee_can_request_a_session_inside_a_mentors_availability(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor', 'academic_year' => 5]);
        $mentee = User::factory()->create(['role' => 'mentee']);
        $subject = Subject::create(['name' => 'Cálculo']);
        $mentor->subjects()->attach($subject->id, ['kind' => 'mentor']);

        $startsAt = today()->addDay()->setTime(16, 0);
        while ($startsAt->dayOfWeek !== 2) {
            $startsAt->addDay();
        }

        $mentor->availabilities()->create([
            'day_of_week' => $startsAt->dayOfWeek,
            'starts_at' => '15:00',
            'ends_at' => '18:00',
        ]);

        $response = $this->actingAs($mentee)->post(route('sessions.store'), [
            'mentor_id' => $mentor->id,
            'subject_id' => $subject->id,
            'starts_at' => $startsAt->format('Y-m-d\TH:i'),
            'duration' => 30,
            'note' => 'Repasar límites.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mentoring_sessions', [
            'mentor_id' => $mentor->id,
            'mentee_id' => $mentee->id,
            'subject_id' => $subject->id,
            'duration' => 30,
            'status' => 'pending',
        ]);
    }

    public function test_a_mentee_cannot_book_outside_a_mentors_availability(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $mentee = User::factory()->create(['role' => 'mentee']);
        $subject = Subject::create(['name' => 'Física']);
        $mentor->subjects()->attach($subject->id, ['kind' => 'mentor']);

        $startsAt = today()->addDay()->setTime(16, 0);
        while ($startsAt->dayOfWeek !== 2) {
            $startsAt->addDay();
        }

        $mentor->availabilities()->create([
            'day_of_week' => $startsAt->dayOfWeek,
            'starts_at' => '09:00',
            'ends_at' => '12:00',
        ]);

        $response = $this->actingAs($mentee)->from(route('mentors.show', $mentor))->post(route('sessions.store'), [
            'mentor_id' => $mentor->id,
            'subject_id' => $subject->id,
            'starts_at' => $startsAt->format('Y-m-d\TH:i'),
            'duration' => 45,
        ]);

        $response->assertRedirect(route('mentors.show', $mentor));
        $response->assertSessionHasErrors('starts_at');
        $this->assertDatabaseCount('mentoring_sessions', 0);
    }

    public function test_a_student_can_save_subjects_to_their_profile(): void
    {
        $mentee = User::factory()->create(['role' => 'mentee']);
        $subject = Subject::create(['name' => 'Programación']);

        $response = $this->actingAs($mentee)->put(route('profile.update'), [
            'name' => $mentee->name,
            'career' => 'Ingeniería',
            'academic_year' => 2,
            'bio' => 'Quiero reforzar mis bases.',
            'subjects' => [$subject->id],
        ]);

        $response->assertRedirect(route('profile.edit'));
        $this->assertDatabaseHas('subject_user', [
            'user_id' => $mentee->id,
            'subject_id' => $subject->id,
            'kind' => 'mentee',
        ]);
    }

    public function test_mentor_search_can_filter_by_subject(): void
    {
        $mentor = User::factory()->create(['name' => 'Carlos Mentor', 'role' => 'mentor']);
        $mentee = User::factory()->create(['role' => 'mentee']);
        $subject = Subject::create(['name' => 'Física']);
        $mentor->subjects()->attach($subject->id, ['kind' => 'mentor']);

        $response = $this->actingAs($mentee)->get(route('mentors.index', ['subject' => 'Fisica']));

        $response->assertOk();
        $response->assertSee('Carlos Mentor');
        $response->assertSee('Física');
    }

    public function test_mentor_search_suggests_the_closest_subject_for_a_typo(): void
    {
        $mentor = User::factory()->create(['name' => 'Ana Mentora', 'role' => 'mentor']);
        $mentee = User::factory()->create(['role' => 'mentee']);
        $subject = Subject::create(['name' => 'Álgebra']);
        $mentor->subjects()->attach($subject->id, ['kind' => 'mentor']);

        $response = $this->actingAs($mentee)->get(route('mentors.index', ['subject' => 'algebrea']));

        $response->assertOk();
        $response->assertSee('Ana Mentora');
        $response->assertSee('No encontré una coincidencia exacta');
        $response->assertSee('Álgebra');
    }

    public function test_mentor_search_shows_available_subjects_before_a_search_is_entered(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $mentee = User::factory()->create(['role' => 'mentee']);
        $subject = Subject::create(['name' => 'Álgebra']);

        $response = $this->actingAs($mentee)->get(route('mentors.index'));

        $response->assertOk();
        $response->assertSee('data-subject="Álgebra"', false);
    }

    public function test_demo_mentor_seeder_creates_six_complete_idempotent_profiles(): void
    {
        $this->seed(MentorDemoSeeder::class);

        $this->assertSame(6, User::where('email', 'like', '%@edumatch.test')->count());

        $mentor = User::where('email', 'laura.mendez@edumatch.test')->firstOrFail();
        $this->assertSame('Laura Méndez', $mentor->name);
        $this->assertSame('Ingeniería en Sistemas', $mentor->career);
        $this->assertCount(3, $mentor->mentorSubjects);
        $this->assertCount(2, $mentor->availabilities);
        $this->assertTrue(Hash::check('MentorDemo2026!', $mentor->password));

        $this->seed(MentorDemoSeeder::class);

        $this->assertSame(6, User::where('email', 'like', '%@edumatch.test')->count());
        $this->assertSame(12, \App\Models\MentorAvailability::whereIn(
            'user_id',
            User::where('email', 'like', '%@edumatch.test')->select('id')
        )->count());
    }

    public function test_demo_review_seeder_creates_visible_reviews_and_is_idempotent(): void
    {
        $this->seed(MentorDemoReviewSeeder::class);

        $mentor = User::where('email', 'laura.mendez@edumatch.test')->firstOrFail();
        $response = $this->actingAs(User::factory()->create(['role' => 'mentee']))
            ->get(route('mentors.show', $mentor));

        $response->assertOk();
        $response->assertSee('Las reseñas de cuentas @edumatch.test son ejemplos ficticios');
        $response->assertSee('Me explicó los límites con ejemplos muy claros.');
        $this->assertSame(12, \App\Models\Review::count());
        $this->assertSame(12, \App\Models\MentoringSession::where('status', 'completed')->count());

        $this->seed(MentorDemoReviewSeeder::class);

        $this->assertSame(12, \App\Models\Review::count());
        $this->assertSame(12, \App\Models\MentoringSession::where('status', 'completed')->count());
    }

    public function test_a_student_cannot_open_another_pair_of_users_session(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $mentee = User::factory()->create(['role' => 'mentee']);
        $outsider = User::factory()->create(['role' => 'mentee']);
        $subject = Subject::create(['name' => 'Química']);
        $session = $mentor->sessionsAsMentor()->create([
            'mentee_id' => $mentee->id,
            'subject_id' => $subject->id,
            'starts_at' => now()->addDay(),
            'duration' => 45,
        ]);

        $response = $this->actingAs($outsider)->get(route('sessions.show', $session));

        $response->assertForbidden();
    }
}
