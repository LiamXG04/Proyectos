@extends('layouts.app')

@section('title', 'Solicitar mentoría')

@section('content')
    <section class="page-section">
        <div class="container narrow-container">
            <a class="back-link" href="{{ route('mentors.show', $mentor) }}">← Volver al perfil</a>
            <div class="page-heading"><span class="eyebrow">Nueva solicitud</span><h1>Coordina con {{ strtok($mentor->name, ' ') }}</h1><p class="muted">Elige una materia y una hora dentro de los espacios publicados por tu mentor.</p></div>
            @if ($mentor->availabilities->isEmpty())
                <div class="notice-box">Este mentor aún no publica horarios disponibles.</div>
            @else
                <div class="panel schedule-summary"><b>Disponibilidad semanal</b><div class="tag-list">@foreach ($mentor->availabilities as $slot)<span class="tag">{{ [0 => 'Dom', 1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb'][$slot->day_of_week] }} · {{ substr($slot->starts_at, 0, 5) }}–{{ substr($slot->ends_at, 0, 5) }}</span>@endforeach</div></div>
                <form method="POST" action="{{ route('sessions.store') }}" class="panel form-panel form-stack">
                    @csrf
                    <input type="hidden" name="mentor_id" value="{{ $mentor->id }}">
                    <label>Materia
                        <select name="subject_id" required>
                            @foreach ($mentor->mentorSubjects as $subject)
                                <option value="{{ $subject->id }}" @selected((string) old('subject_id') === (string) $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Fecha y hora
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" min="{{ now()->format('Y-m-d\TH:i') }}" required>
                        <small class="field-hint">Selecciona un día de la semana publicado arriba y una hora dentro de ese bloque.</small>
                    </label>
                    <label>Duración
                        <select name="duration" required><option value="45" @selected(old('duration', '45') === '45')>45 minutos</option><option value="30" @selected(old('duration') === '30')>30 minutos</option></select>
                    </label>
                    <label>¿Qué tema te gustaría trabajar?<textarea name="note" rows="3" maxlength="1000" placeholder="Cuéntale al mentor qué necesitas repasar.">{{ old('note') }}</textarea></label>
                    <div class="form-actions"><a class="button button-light" href="{{ route('mentors.show', $mentor) }}">Cancelar</a><button class="button" type="submit">Enviar solicitud</button></div>
                </form>
            @endif
        </div>
    </section>
@endsection
