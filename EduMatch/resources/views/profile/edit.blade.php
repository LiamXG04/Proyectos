@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
    <section class="page-section">
        <div class="container narrow-container">
            <div class="page-heading"><span class="eyebrow">Tu presentación</span><h1>Mi perfil</h1><p class="muted">Cuéntale a la comunidad qué estudias y en qué puedes recibir u ofrecer apoyo.</p></div>
            <form method="POST" action="{{ route('profile.update') }}" class="panel form-panel form-stack">
                @csrf
                @method('PUT')
                <div class="form-row">
                    <label>Nombre completo<input type="text" name="name" value="{{ old('name', $user->name) }}" required></label>
                    <label>Correo electrónico<input type="email" value="{{ $user->email }}" disabled><small class="field-hint">El correo no se puede cambiar desde el perfil.</small></label>
                </div>
                <div class="form-row">
                    <label>Carrera<input type="text" name="career" value="{{ old('career', $user->career) }}" required></label>
                    <label>Año académico
                        <select name="academic_year" required>
                            @for ($year = 1; $year <= 10; $year++)
                                <option value="{{ $year }}" @selected((string) old('academic_year', $user->academic_year) === (string) $year)>{{ $year }}{{ $year === 1 ? 'er' : '°' }} año</option>
                            @endfor
                        </select>
                    </label>
                </div>
                <label>Sobre mí<textarea name="bio" rows="4" maxlength="1000" placeholder="Comparte brevemente tus intereses o cómo te gusta aprender.">{{ old('bio', $user->bio) }}</textarea></label>
                <fieldset class="subject-fieldset">
                    <legend>{{ $user->role === 'mentor' ? 'Materias que puedo enseñar' : 'Materias en las que quiero apoyo' }}</legend>
                    <p class="field-hint">Puedes seleccionar varias opciones.</p>
                    <div class="subject-grid">
                        @foreach ($subjects as $subject)
                            <label class="subject-option"><input type="checkbox" name="subjects[]" value="{{ $subject->id }}" @checked(in_array($subject->id, old('subjects', $selectedSubjects)))><span>{{ $subject->name }}</span></label>
                        @endforeach
                    </div>
                </fieldset>
                <div class="form-actions"><a class="button button-light" href="{{ route('dashboard') }}">Cancelar</a><button class="button" type="submit">Guardar perfil</button></div>
            </form>
        </div>
    </section>
@endsection
