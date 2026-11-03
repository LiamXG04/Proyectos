@extends('layouts.app')

@section('title', 'Mentoría que conecta')

@section('content')
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-copy">
                <span class="eyebrow"><span class="eyebrow-dot"></span> Aprende en comunidad</span>
                <h1>Las materias difíciles se entienden <span>mejor en equipo.</span></h1>
                <p>Conecta con estudiantes que ya recorrieron el camino. Encuentra mentoría académica cercana, clara y a tu ritmo.</p>
                <div class="hero-actions">
                    <a class="button" href="{{ route('register') }}">Encuentra tu mentor <span aria-hidden="true">→</span></a>
                    <a class="button button-light" href="{{ route('register') }}">Quiero ser mentor</a>
                </div>
                <div class="hero-note"><span class="avatar-stack"><i>A</i><i>M</i><i>J</i></span><span>Apoyo entre estudiantes, para estudiantes.</span></div>
            </div>
            <div class="hero-art" aria-label="Ilustración de una sesión de estudio">
                <div class="art-orbit orbit-one"></div>
                <div class="art-orbit orbit-two"></div>
                <div class="art-card art-card-main">
                    <div class="art-card-top"><span class="live-dot"></span> SESIÓN DE ESTUDIO <span class="art-time">45 min</span></div>
                    <div class="art-subject">Cálculo diferencial</div>
                    <div class="art-lesson">Límites y continuidad</div>
                    <div class="lesson-line"><span></span></div>
                    <div class="art-people">
                        <div class="person"><span class="person-avatar person-one">CM</span><span><b>Carla M.</b><small>Mentora · Ingeniería</small></span></div>
                        <div class="person-check">✓</div>
                    </div>
                </div>
                <div class="floating-note"><span class="floating-star">★</span><span><b>4.9</b><small> de 5 · 18 reseñas</small></span></div>
                <div class="floating-tag">Tu próxima idea empieza aquí</div>
            </div>
        </div>
    </section>

    <section class="section section-how">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow">Así funciona</span>
                <h2>Un buen encuentro puede cambiar tu semestre.</h2>
                <p>Menos vueltas para encontrar ayuda. Más tiempo para aprender.</p>
            </div>
            <div class="steps-grid">
                <article class="step-card"><span class="step-number">01</span><div class="step-icon">⌕</div><h3>Busca una materia</h3><p>Explora mentores por las materias que necesitas reforzar.</p></article>
                <article class="step-card"><span class="step-number">02</span><div class="step-icon">◷</div><h3>Coordina tu sesión</h3><p>Elige un horario disponible y comparte qué tema quieres estudiar.</p></article>
                <article class="step-card"><span class="step-number">03</span><div class="step-icon">↗</div><h3>Aprendan juntos</h3><p>Conversa por el chat, completa la sesión y comparte tu experiencia.</p></article>
            </div>
        </div>
    </section>

    <section class="bottom-cta">
        <div class="container bottom-cta-inner">
            <div><span class="eyebrow eyebrow-light">Una comunidad que comparte</span><h2>Lo que ya aprendiste puede impulsar a alguien más.</h2></div>
            <a class="button button-white" href="{{ route('register') }}">Únete a EduMatch <span aria-hidden="true">→</span></a>
        </div>
    </section>
@endsection
