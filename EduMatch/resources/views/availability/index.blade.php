@extends('layouts.app')

@section('title', 'Mi disponibilidad')

@section('content')
    <section class="page-section">
        <div class="container narrow-container">
            <div class="page-heading"><span class="eyebrow">Organiza tu tiempo</span><h1>Mi disponibilidad</h1><p class="muted">Agrega los bloques semanales en los que puedes ofrecer mentorías. Las sesiones duran 30 o 45 minutos.</p></div>
            <form method="POST" action="{{ route('availability.store') }}" class="panel form-panel form-stack availability-form">
                @csrf
                <h2>Agregar horario</h2>
                <div class="form-row form-row-three">
                    <label>Día
                        <select name="day_of_week" required>
                            @foreach ($days as $number => $day)
                                <option value="{{ $number }}" @selected((string) old('day_of_week') === (string) $number)>{{ $day }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Desde<input type="time" name="starts_at" value="{{ old('starts_at') }}" required></label>
                    <label>Hasta<input type="time" name="ends_at" value="{{ old('ends_at') }}" required></label>
                </div>
                <button class="button" type="submit">Agregar horario</button>
            </form>
            <div class="section-title-row"><div><span class="eyebrow">Tu agenda habitual</span><h2>Horarios publicados</h2></div></div>
            @forelse ($availabilities as $slot)
                <div class="availability-manage-row panel"><div><b>{{ $days[$slot->day_of_week] }}</b><span>{{ substr($slot->starts_at, 0, 5) }} – {{ substr($slot->ends_at, 0, 5) }}</span></div><form method="POST" action="{{ route('availability.destroy', $slot) }}">@csrf @method('DELETE')<button class="button button-danger-light button-small" type="submit">Eliminar</button></form></div>
            @empty
                <div class="empty-state"><span class="empty-icon">◷</span><b>Sin horarios por ahora</b><p>Agrega al menos un horario para que los estudiantes puedan solicitar una sesión.</p></div>
            @endforelse
        </div>
    </section>
@endsection
