@extends('layouts.app')

@section('title', 'Buscar mentores')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/subject-search.css') }}">
@endpush

@section('content')
    <section class="page-section">
        <div class="container">
            <div class="page-heading"><span class="eyebrow">Aprender en comunidad</span><h1>Encuentra a tu mentor</h1><p class="muted">Busca por materia y conoce estudiantes que pueden acompañarte.</p></div>
            <form method="GET" action="{{ route('mentors.index') }}" class="search-bar" id="subject-search-form">
                <div class="subject-search-wrap">
                    <label class="search-field" for="subject-search"><span aria-hidden="true">⌕</span><input type="search" id="subject-search" name="subject" value="{{ $subjectSearch }}" placeholder="¿Qué materia necesitas reforzar?" autocomplete="off" role="combobox" aria-autocomplete="list" aria-controls="subject-suggestions" aria-expanded="false"></label>
                    <ul class="subject-suggestions" id="subject-suggestions" role="listbox" hidden>
                        @foreach ($availableSubjects as $subject)
                            <li role="option"><button type="button" class="subject-suggestion" data-subject="{{ $subject->name }}">{{ $subject->name }}</button></li>
                        @endforeach
                    </ul>
                </div>
                <button class="button" type="submit">Buscar</button>
            </form>
            @if ($subjectSuggestion)
                <div class="search-suggestion" role="status">
                    No encontré una coincidencia exacta para “{{ $subjectSearch }}”. Mostrando materias similares a <strong>{{ $subjectSuggestion->name }}</strong>.
                    <a href="{{ route('mentors.index', ['subject' => $subjectSuggestion->name]) }}">Buscar “{{ $subjectSuggestion->name }}” exactamente</a>
                </div>
            @endif
            <div class="results-heading"><b>{{ $mentors->total() }} {{ $mentors->total() === 1 ? 'mentor disponible' : 'mentores disponibles' }}</b>@if ($subjectSearch)<a class="text-link" href="{{ route('mentors.index') }}">Limpiar búsqueda ×</a>@endif</div>
            @if ($mentors->isEmpty())
                <div class="empty-state empty-large"><span class="empty-icon">⌕</span><b>No encontramos mentores con esa materia</b><p>Prueba con otro nombre o vuelve a ver todos los mentores.</p><a class="button button-light" href="{{ route('mentors.index') }}">Ver todos</a></div>
            @else
                <div class="mentor-grid">
                    @foreach ($mentors as $mentor)
                        <article class="mentor-card">
                            <div class="mentor-card-head"><span class="mentor-avatar">{{ strtoupper(substr($mentor->name, 0, 1)) }}</span><span class="mentor-rating">★ {{ number_format((float) ($mentor->reviews_received_avg_rating ?? 0), 1) }} <small>({{ $mentor->reviews_received_count }} reseñas)</small></span></div>
                            <h2>{{ $mentor->name }}</h2><p class="mentor-meta">{{ $mentor->career ?: 'Estudiante universitario' }} · {{ $mentor->academic_year }}° año</p>
                            <div class="tag-list">@forelse ($mentor->mentorSubjects as $subject)<span class="tag">{{ $subject->name }}</span>@empty<span class="field-hint">Aún no agrega materias</span>@endforelse</div>
                            <p class="mentor-bio">{{ $mentor->bio ?: 'Estudiante con ganas de compartir conocimientos y aprender en comunidad.' }}</p>
                            <a class="button button-outline button-full" href="{{ route('mentors.show', $mentor) }}">Ver perfil y horarios <span aria-hidden="true">→</span></a>
                        </article>
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $mentors->links() }}</div>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/subject-search.js') }}" defer></script>
@endpush
