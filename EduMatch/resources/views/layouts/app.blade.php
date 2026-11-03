<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EduMatch') · Aprende, comparte, crece</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/refinements.css') }}">
    @stack('styles')
</head>
<body>
    <header class="site-header">
        <div class="container nav-wrap">
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark">E</span> EduMatch</a>
            <nav class="main-nav" aria-label="Navegación principal">
                @auth
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Inicio</a>
                    <a href="{{ route('mentors.index') }}" class="{{ request()->routeIs('mentors.*') ? 'active' : '' }}">Mentores</a>
                    <a href="{{ route('sessions.index') }}" class="{{ request()->routeIs('sessions.*') ? 'active' : '' }}">Mis sesiones</a>
                    @if (auth()->user()->role === 'mentor')
                        <a href="{{ route('availability.index') }}" class="{{ request()->routeIs('availability.*') ? 'active' : '' }}">Disponibilidad</a>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">Mi perfil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="nav-logout" type="submit">Salir</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Iniciar sesión</a>
                    <a class="button button-small" href="{{ route('register') }}">Crear cuenta</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-error" role="alert">
                    <strong>Revisa estos datos:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <a class="brand brand-footer" href="{{ route('home') }}"><span class="brand-mark">E</span> EduMatch</a>
            <span>Aprende · Comparte · Crece</span>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
