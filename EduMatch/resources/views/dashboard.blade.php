@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <section class="page-section">
        <div class="container">
            <div class="welcome-row">
                <div><span class="eyebrow">Tu espacio de aprendizaje</span><h1>Hola, {{ auth()->user()->name }} <span class="wave">✦</span></h1><p class="muted">Un paso pequeño hoy puede hacer una gran diferencia.</p></div>
                <span class="role-pill">{{ auth()->user()->role === 'mentor' ? 'Mentor' : 'Mentee' }}</span>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-main">
                    <article class="panel panel-highlight">
                        <div class="panel-copy">
                            <span class="eyebrow eyebrow-light">{{ auth()->user()->role === 'mentor' ? 'Comparte lo que sabes' : 'Encuentra apoyo académico' }}</span>
                            <h2>{{ auth()->user()->role === 'mentor' ? 'Tu experiencia puede abrir nuevas puertas.' : '¿Qué tema quieres entender mejor?' }}</h2>
                            <p>{{ auth()->user()->role === 'mentor' ? 'Completa tu perfil y agrega horarios para que otros estudiantes puedan encontrarte.' : 'Busca estudiantes que ya dominan esa materia y coordina una sesión.' }}</p>
                            <a class="button button-white" href="{{ auth()->user()->role === 'mentor' ? route('availability.index') : route('mentors.index') }}">{{ auth()->user()->role === 'mentor' ? 'Configurar disponibilidad' : 'Buscar mentores' }} <span aria-hidden="true">→</span></a>
                        </div>
                        <div class="panel-decoration"><span>✦</span><i></i><b></b></div>
                    </article>

                    <div class="section-title-row"><div><span class="eyebrow">Agenda</span><h2>Próximas sesiones</h2></div><a class="text-link" href="{{ route('sessions.index') }}">Ver todas <span aria-hidden="true">→</span></a></div>
                    @forelse ($sessions as $session)
                        <a class="session-card" href="{{ route('sessions.show', $session) }}">
                            <span class="session-date"><b>{{ $session->starts_at->format('d') }}</b><small>{{ $session->starts_at->translatedFormat('M') }}</small></span>
                            <span class="session-info"><b>{{ $session->subject->name }}</b><small>{{ auth()->id() === $session->mentor_id ? 'Con '.$session->mentee->name : 'Con '.$session->mentor->name }} · {{ $session->starts_at->format('H:i') }}</small></span>
                            <span class="status status-{{ $session->status }}">{{ ['pending' => 'Pendiente', 'accepted' => 'Confirmada'][$session->status] ?? ucfirst($session->status) }}</span>
                        </a>
                    @empty
                        <div class="empty-state"><span class="empty-icon">◷</span><b>Aún no tienes sesiones</b><p>Cuando coordines una mentoría, aparecerá aquí.</p></div>
                    @endforelse
                </div>

                <aside class="dashboard-side">
                    <article class="panel side-stat"><span class="eyebrow">Actividad</span><strong>{{ $pendingRequests }}</strong><p>{{ $pendingRequests === 1 ? 'solicitud pendiente' : 'solicitudes pendientes' }}</p><a class="text-link" href="{{ route('sessions.index') }}">Revisar sesiones →</a></article>
                    <article class="panel side-profile"><span class="eyebrow">Tu perfil</span><h3>{{ auth()->user()->career ?: 'Agrega tu carrera' }}</h3><p>{{ auth()->user()->academic_year ? auth()->user()->academic_year.'° año' : 'Completa tu año académico' }}</p><a class="button button-outline button-full" href="{{ route('profile.edit') }}">Editar perfil</a></article>
                    @if (auth()->user()->role === 'mentee')
                        <article class="panel recommendation-panel"><span class="eyebrow">Comunidad EduMatch</span><h3>Estudiantes que comparten lo que saben.</h3>
                            @foreach ($mentors as $mentor)
                                <a class="mini-mentor" href="{{ route('mentors.show', $mentor) }}"><span class="mini-avatar">{{ strtoupper(substr($mentor->name, 0, 1)) }}</span><span><b>{{ $mentor->name }}</b><small>{{ $mentor->career }}</small></span><span class="mini-rating">★ {{ number_format((float) ($mentor->reviews_received_avg_rating ?? 0), 1) }}</span></a>
                            @endforeach
                        </article>
                    @endif
                </aside>
            </div>
        </div>
    </section>
@endsection
