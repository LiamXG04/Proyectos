<?php

namespace App\Http\Controllers;

use App\Models\MentorAvailability;
use App\Models\MentoringSession;
use App\Models\Message;
use App\Models\Review;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EduMatchController extends Controller
{
    private const DAYS = [
        0 => 'Domingo',
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
    ];

    public function home(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('home');
    }

    public function dashboard(): View
    {
        $user = auth()->user();
        $sessions = MentoringSession::with(['mentor', 'mentee', 'subject'])
            ->where(function ($query) use ($user) {
                $query->where('mentor_id', $user->id)->orWhere('mentee_id', $user->id);
            })
            ->whereIn('status', ['pending', 'accepted'])
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        $pendingRequests = $user->role === 'mentor'
            ? $user->sessionsAsMentor()->where('status', 'pending')->count()
            : $user->sessionsAsMentee()->where('status', 'pending')->count();

        $mentors = User::query()
            ->where('role', 'mentor')
            ->with('mentorSubjects')
            ->withAvg('reviewsReceived', 'rating')
            ->orderByDesc('reviews_received_avg_rating')
            ->limit(3)
            ->get();

        return view('dashboard', compact('sessions', 'pendingRequests', 'mentors'));
    }

    public function editProfile(): View
    {
        $user = auth()->user()->load('subjects');
        $subjects = Subject::orderBy('name')->get();
        $selectedSubjects = $user->subjects->where('pivot.kind', $user->role)->pluck('id')->all();

        return view('profile.edit', compact('user', 'subjects', 'selectedSubjects'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'career' => ['required', 'string', 'max:120'],
            'academic_year' => ['required', 'integer', 'between:1,10'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'subjects' => ['array'],
            'subjects.*' => ['integer', 'distinct', 'exists:subjects,id'],
        ]);

        if ($user->role === 'mentor' && empty($data['subjects'])) {
            return back()->withErrors(['subjects' => 'Selecciona al menos una materia que puedas enseñar.'])->withInput();
        }

        $user->update([
            'name' => $data['name'],
            'career' => $data['career'],
            'academic_year' => $data['academic_year'],
            'bio' => $data['bio'] ?? null,
        ]);

        $user->subjects()->wherePivot('kind', $user->role)->syncWithPivotValues(
            $data['subjects'] ?? [],
            ['kind' => $user->role]
        );

        return redirect()->route('profile.edit')->with('success', 'Tu perfil se guardó correctamente.');
    }

    public function mentors(Request $request): View
    {
        $subjectSearch = trim((string) $request->query('subject', ''));
        $availableSubjects = Subject::query()
            ->orderBy('name')
            ->get(['id', 'name']);
        $matchingSubjects = $availableSubjects;
        $subjectSuggestion = null;

        if ($subjectSearch !== '') {
            $normalizedSearch = $this->normalizeSubjectSearch($subjectSearch);
            $exactMatch = $availableSubjects->first(
                fn (Subject $subject) => $this->normalizeSubjectSearch($subject->name) === $normalizedSearch
            );
            $partialMatches = $availableSubjects->filter(function (Subject $subject) use ($normalizedSearch) {
                $normalizedName = $this->normalizeSubjectSearch($subject->name);

                return str_contains($normalizedName, $normalizedSearch);
            });

            if ($exactMatch) {
                $matchingSubjects = collect([$exactMatch]);
            } elseif ($partialMatches->isNotEmpty()) {
                $matchingSubjects = $partialMatches;
            } else {
                $closestSubject = $availableSubjects
                    ->map(fn (Subject $subject) => [
                        'subject' => $subject,
                        'distance' => levenshtein(
                            $normalizedSearch,
                            $this->normalizeSubjectSearch($subject->name)
                        ),
                    ])
                    ->sortBy('distance')
                    ->first();

                $allowedDistance = max(1, (int) ceil(strlen($normalizedSearch) * 0.25));

                if ($closestSubject && strlen($normalizedSearch) >= 3 && $closestSubject['distance'] <= $allowedDistance) {
                    $matchingSubjects = collect([$closestSubject['subject']]);
                    $subjectSuggestion = $closestSubject['subject'];
                } else {
                    $matchingSubjects = collect();
                }
            }
        }

        $mentors = User::query()
            ->where('role', 'mentor')
            ->whereKeyNot(auth()->id())
            ->with('mentorSubjects')
            ->withAvg('reviewsReceived', 'rating')
            ->withCount('reviewsReceived')
            ->when($subjectSearch !== '', function ($query) use ($matchingSubjects) {
                $query->whereHas('mentorSubjects', function ($subjects) use ($matchingSubjects) {
                    $subjects->whereIn('subjects.id', $matchingSubjects->pluck('id'));
                });
            })
            ->orderByDesc('reviews_received_avg_rating')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('mentors.index', compact('mentors', 'subjectSearch', 'availableSubjects', 'subjectSuggestion'));
    }

    private function normalizeSubjectSearch(string $subject): string
    {
        return Str::lower(Str::ascii(trim($subject)));
    }

    public function showMentor(User $mentor): View
    {
        abort_unless($mentor->role === 'mentor', 404);
        abort_if($mentor->is(auth()->user()), 404);

        $mentor->load(['mentorSubjects', 'availabilities'])
            ->loadAvg('reviewsReceived', 'rating')
            ->loadCount('reviewsReceived');
        $reviews = Review::with(['mentee', 'session.subject'])
            ->where('mentor_id', $mentor->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('mentors.show', compact('mentor', 'reviews'));
    }

    public function availability(): View
    {
        abort_unless(auth()->user()->role === 'mentor', 403);

        $availabilities = auth()->user()->availabilities;

        return view('availability.index', ['availabilities' => $availabilities, 'days' => self::DAYS]);
    }

    public function storeAvailability(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'mentor', 403);

        $data = $request->validate([
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
        ]);

        $exists = $request->user()->availabilities()
            ->where('day_of_week', $data['day_of_week'])
            ->where('starts_at', $data['starts_at'])
            ->where('ends_at', $data['ends_at'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['starts_at' => 'Ese horario ya está registrado.'])->withInput();
        }

        $request->user()->availabilities()->create($data);

        return back()->with('success', 'Horario agregado a tu disponibilidad.');
    }

    public function deleteAvailability(MentorAvailability $availability): RedirectResponse
    {
        abort_unless($availability->user_id === auth()->id(), 403);
        $availability->delete();

        return back()->with('success', 'Horario eliminado.');
    }

    public function createSession(User $mentor): View
    {
        abort_unless(auth()->user()->role === 'mentee', 403);
        abort_unless($mentor->role === 'mentor', 404);
        abort_if($mentor->is(auth()->user()), 404);

        $mentor->load(['mentorSubjects', 'availabilities']);

        return view('sessions.create', compact('mentor'));
    }

    public function storeSession(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'mentee', 403);
        $data = $request->validate([
            'mentor_id' => ['required', 'integer', 'exists:users,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'starts_at' => ['required', 'date', 'after:now'],
            'duration' => ['required', 'integer', 'in:30,45'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $mentor = User::where('role', 'mentor')->findOrFail($data['mentor_id']);
        abort_if($mentor->is($request->user()), 403);
        abort_unless($mentor->mentorSubjects()->whereKey($data['subject_id'])->exists(), 422, 'El mentor no ofrece esa materia.');

        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = $startsAt->copy()->addMinutes((int) $data['duration']);
        $availability = $mentor->availabilities()
            ->where('day_of_week', $startsAt->dayOfWeek)
            ->where('starts_at', '<=', $startsAt->format('H:i:s'))
            ->where('ends_at', '>=', $endsAt->format('H:i:s'))
            ->first();

        if (! $availability) {
            return back()->withErrors(['starts_at' => 'El horario elegido no está dentro de la disponibilidad del mentor.'])->withInput();
        }

        $hasConflict = MentoringSession::query()
            ->where(function ($query) use ($mentor, $request) {
                $query->where('mentor_id', $mentor->id)
                    ->orWhere('mentee_id', $request->user()->id);
            })
            ->whereDate('starts_at', $startsAt->toDateString())
            ->whereIn('status', ['pending', 'accepted'])
            ->get()
            ->contains(function (MentoringSession $session) use ($startsAt, $endsAt) {
                $existingEnd = $session->starts_at->copy()->addMinutes($session->duration);

                return $startsAt->lt($existingEnd) && $endsAt->gt($session->starts_at);
            });

        if ($hasConflict) {
            return back()->withErrors(['starts_at' => 'Ese horario se cruza con otra sesión. Elige otro espacio disponible.'])->withInput();
        }

        $session = DB::transaction(function () use ($data, $mentor, $request, $startsAt) {
            return MentoringSession::create([
                'mentor_id' => $mentor->id,
                'mentee_id' => $request->user()->id,
                'subject_id' => $data['subject_id'],
                'starts_at' => $startsAt,
                'duration' => $data['duration'],
                'note' => $data['note'] ?? null,
            ]);
        });

        return redirect()->route('sessions.show', $session)->with('success', 'Solicitud enviada. Puedes coordinar los detalles por el chat de la sesión.');
    }

    public function sessions(): View
    {
        $user = auth()->user();
        $sessions = MentoringSession::with(['mentor', 'mentee', 'subject', 'review'])
            ->where(function ($query) use ($user) {
                $query->where('mentor_id', $user->id)->orWhere('mentee_id', $user->id);
            })
            ->orderByDesc('starts_at')
            ->paginate(15);

        return view('sessions.index', compact('sessions'));
    }

    public function showSession(MentoringSession $session): View
    {
        $this->authorizeParticipant($session);
        $session->load(['mentor', 'mentee', 'subject', 'messages', 'review']);

        return view('sessions.show', compact('session'));
    }

    public function sendMessage(Request $request, MentoringSession $session): RedirectResponse
    {
        $this->authorizeParticipant($session);
        abort_if($session->status === 'cancelled', 403);

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        Message::create([
            'mentoring_session_id' => $session->id,
            'sender_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return redirect()->route('sessions.show', $session)->withFragment('conversation');
    }

    public function updateSessionStatus(Request $request, MentoringSession $session, string $status): RedirectResponse
    {
        $this->authorizeParticipant($session);
        $user = $request->user();

        if ($status === 'accepted') {
            abort_unless($session->mentor_id === $user->id && $user->role === 'mentor', 403);
            abort_unless($session->status === 'pending', 422, 'Esta solicitud ya fue atendida.');
            $this->abortIfMentorHasConflict($session);
        } elseif ($status === 'completed') {
            abort_unless($session->mentor_id === $user->id && $user->role === 'mentor', 403);
            abort_unless($session->status === 'accepted', 422, 'Solo puedes completar una sesión aceptada.');
            abort_if($session->starts_at->copy()->addMinutes($session->duration)->isFuture(), 422, 'La sesión todavía no ha terminado.');
        } elseif ($status === 'cancelled') {
            abort_unless(in_array($session->status, ['pending', 'accepted'], true), 422, 'Esta sesión ya no se puede cancelar.');
        } else {
            abort(404);
        }

        $session->update(['status' => $status]);

        return back()->with('success', 'El estado de la sesión se actualizó.');
    }

    public function storeReview(Request $request, MentoringSession $session): RedirectResponse
    {
        abort_unless($session->mentee_id === $request->user()->id, 403);
        abort_unless($session->status === 'completed', 422, 'Solo puedes calificar una sesión completada.');
        abort_unless(! $session->review()->exists(), 422, 'Esta sesión ya tiene una calificación.');

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $session->review()->create([
            'mentor_id' => $session->mentor_id,
            'mentee_id' => $session->mentee_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return back()->with('success', 'Gracias. Tu calificación ayuda a otros estudiantes a elegir mentor.');
    }

    private function authorizeParticipant(MentoringSession $session): void
    {
        abort_unless(in_array(auth()->id(), [$session->mentor_id, $session->mentee_id], true), 403);
    }

    private function abortIfMentorHasConflict(MentoringSession $session): void
    {
        $sessionEnd = $session->starts_at->copy()->addMinutes($session->duration);
        $hasConflict = MentoringSession::query()
            ->where('mentor_id', $session->mentor_id)
            ->whereKeyNot($session->id)
            ->whereDate('starts_at', $session->starts_at->toDateString())
            ->where('status', 'accepted')
            ->get()
            ->contains(function (MentoringSession $other) use ($session, $sessionEnd) {
                $otherEnd = $other->starts_at->copy()->addMinutes($other->duration);

                return $session->starts_at->lt($otherEnd) && $sessionEnd->gt($other->starts_at);
            });

        abort_if($hasConflict, 422, 'El mentor ya tiene otra sesión aceptada en ese horario.');
    }
}
