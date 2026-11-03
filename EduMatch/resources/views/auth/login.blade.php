@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
    <section class="auth-section">
        <div class="auth-card">
            <span class="eyebrow">Qué bueno verte</span>
            <h1>Inicia sesión</h1>
            <p class="muted">Vuelve a conectar con tu comunidad de aprendizaje.</p>
            <form method="POST" action="{{ route('login') }}" class="form-stack">
                @csrf
                <label>Correo electrónico
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                </label>
                <label>Contraseña
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>
                <label class="checkbox-label"><input type="checkbox" name="remember" value="1"> Recordarme</label>
                <button class="button button-full" type="submit">Entrar a EduMatch</button>
            </form>
            <p class="auth-switch">¿Aún no tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
        </div>
    </section>
@endsection
