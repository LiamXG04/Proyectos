@extends('layouts.app')

@section('title', 'Crear cuenta')

@section('content')
    <section class="auth-section">
        <div class="auth-card auth-card-wide">
            <span class="eyebrow">Empieza por aquí</span>
            <h1>Crea tu cuenta</h1>
            <p class="muted">Un espacio para aprender y compartir lo que sabes.</p>
            <form method="POST" action="{{ route('register') }}" class="form-stack">
                @csrf
                <label>Nombre completo
                    <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required>
                </label>
                <label>Correo electrónico
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                </label>
                <div class="form-row">
                    <label>Quiero participar como
                        <select name="role" required>
                            <option value="">Selecciona un rol</option>
                            <option value="mentee" @selected(old('role') === 'mentee')>Mentee · busco apoyo</option>
                            <option value="mentor" @selected(old('role') === 'mentor')>Mentor · quiero enseñar</option>
                        </select>
                    </label>
                    <label>Año académico
                        <select name="academic_year" required>
                            <option value="">Año</option>
                            @for ($year = 1; $year <= 10; $year++)
                                <option value="{{ $year }}" @selected((string) old('academic_year') === (string) $year)>{{ $year }}{{ $year === 1 ? 'er' : '°' }} año</option>
                            @endfor
                        </select>
                    </label>
                </div>
                <label>Carrera
                    <input type="text" name="career" value="{{ old('career') }}" placeholder="Ej. Ingeniería en Sistemas" required>
                </label>
                <div class="form-row">
                    <label>Contraseña
                        <input type="password" name="password" autocomplete="new-password" required>
                    </label>
                    <label>Confirma tu contraseña
                        <input type="password" name="password_confirmation" autocomplete="new-password" required>
                    </label>
                </div>
                <button class="button button-full" type="submit">Crear mi cuenta</button>
            </form>
            <p class="auth-switch">¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
        </div>
    </section>
@endsection
