@extends('layouts.app')

@section('title', 'Mis sesiones')

@section('content')
    <section class="page-section">
        <div class="container">
            <div class="page-heading"><span class="eyebrow">Tu recorrido</span><h1>Mis sesiones</h1><p class="muted">Revisa solicitudes, coordina detalles y vuelve a tus mentorías anteriores.</p></div>
            @forelse ($sessions as $session)
                <a class="session-card session-list-card" href="{{ route('sessions.show', $session) }}">
                    <span class="session-date"><b>{{ $session->starts_at->format('d') }}</b><small>{{ $session->starts_at->translatedFormat('M') }}</small></span>
                    <span class="session-info"><b>{{ $session->subject->name }}</b><small>{{ auth()->id() === $session->mentor_id ? 'Mentee: '.$session->mentee->name : 'Mentor: '.$session->mentor->name }} · {{ $session->starts_at->format('d/m/Y · H:i') }} · {{ $session->duration }} min</small></span>
                    <span class="status status-{{ $session->status }}">{{ ['pending' => 'Pendiente', 'accepted' => 'Confirmada', 'completed' => 'Completada', 'cancelled' => 'Cancelada'][$session->status] }}</span>
                </a>
            @empty
                <div class="empty-state empty-large"><span class="empty-icon">◷</span><b>Aún no tienes sesiones</b><p>Busca un mentor o configura tus horarios para empezar.</p><a class="button" href="{{ auth()->user()->role === 'mentee' ? route('mentors.index') : route('availability.index') }}">{{ auth()->user()->role === 'mentee' ? 'Buscar mentores' : 'Configurar disponibilidad' }}</a></div>
            @endforelse
            <div class="pagination-wrap">{{ $sessions->links() }}</div>
        </div>
    </section>
@endsection
