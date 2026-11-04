@extends('layouts.app')

@section('title', 'Perfil de mentor')

@section('content')
    <section class="page-section">
        <div class="container profile-grid">
            <div class="profile-main">
                <a class="back-link" href="{{ route('mentors.index') }}">← Volver a mentores</a>
                <article class="panel mentor-profile-card">
                    <span class="mentor-avatar mentor-avatar-large">{{ strtoupper(substr($mentor->name, 0, 1)) }}</span>
                    <div class="profile-title"><span class="eyebrow">Mentor EduMatch</span><h1>{{ $mentor->name }}</h1><p>{{ $mentor->career }} · {{ $mentor->academic_year }}° año</p></div>
                    <div class="profile-rating"><b>★ {{ number_format((float) ($mentor->reviews_received_avg_rating ?? 0), 1) }}</b><span>{{ $mentor->reviews_received_count }} reseñas</span></div>
                    <div class="profile-divider"></div>
                    <h2>Materias que puede enseñar</h2>
                    <div class="tag-list">@forelse ($mentor->mentorSubjects as $subject)<span class="tag">{{ $subject->name }}</span>@empty<p class="muted">Este mentor aún no ha agregado materias.</p>@endforelse</div>
                    <h2>Sobre {{ strtok($mentor->name, ' ') }}</h2>
                    <p class="profile-bio">{{ $mentor->bio ?: 'Este estudiante está listo para compartir lo que sabe y aprender en comunidad.' }}</p>
                    <h2>Disponibilidad semanal</h2>
                    @forelse ($mentor->availabilities as $availability)
                        <div class="availability-row"><b>{{ [0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado'][$availability->day_of_week] }}</b><span>{{ substr($availability->starts_at, 0, 5) }} – {{ substr($availability->ends_at, 0, 5) }}</span></div>
                    @empty
                        <p class="muted">El mentor todavía no ha publicado horarios.</p>
                    @endforelse
                </article>
                <div class="section-title-row review-title"><div><span class="eyebrow">Experiencias</span><h2>Lo que cuentan otros estudiantes</h2></div></div>
                @if ($reviews->contains(fn ($review) => str_ends_with($review->mentee->email, '@edumatch.test')))
                    <p class="demo-reviews-note">Las reseñas de cuentas @edumatch.test son ejemplos ficticios para mostrar la plataforma.</p>
                @endif
                @forelse ($reviews as $review)
                    <article class="panel review-card"><div class="review-head"><b>{{ $review->mentee->name }}</b><span class="mentor-rating">★ {{ $review->rating }}.0</span></div><small>{{ $review->session->subject->name }} · {{ $review->created_at->format('d/m/Y') }}@if (str_ends_with($review->mentee->email, '@edumatch.test')) · Demo @endif</small><p>{{ $review->comment ?: 'El estudiante calificó esta sesión.' }}</p></article>
                @empty
                    <div class="empty-state"><b>Aún no hay reseñas</b><p>Las calificaciones aparecerán aquí después de las sesiones.</p></div>
                @endforelse
            </div>
            <aside class="panel booking-sidebar">
                <span class="eyebrow">¿Te gustaría aprender?</span><h2>Coordina una sesión</h2>
                <p class="muted">Envía una solicitud y acuerden los detalles por el chat.</p>
                @if (auth()->user()->role === 'mentee')
                    <a class="button button-full" href="{{ route('sessions.create', $mentor) }}">Solicitar mentoría <span aria-hidden="true">→</span></a>
                @else
                    <div class="notice-box">Las solicitudes de mentoría están disponibles para cuentas mentee.</div>
                @endif
                <p class="field-hint">Duración disponible: 30 o 45 minutos.</p>
            </aside>
        </div>
    </section>
@endsection
