@extends('layouts.app')

@section('title', 'Detalle de sesión')

@section('content')
    <section class="page-section">
        <div class="container session-detail-grid">
            <div>
                <a class="back-link" href="{{ route('sessions.index') }}">← Volver a mis sesiones</a>
                <article class="panel session-detail-card">
                    <div class="session-detail-heading"><div><span class="eyebrow">Sesión de mentoría</span><h1>{{ $session->subject->name }}</h1></div><span class="status status-{{ $session->status }}">{{ ['pending' => 'Pendiente', 'accepted' => 'Confirmada', 'completed' => 'Completada', 'cancelled' => 'Cancelada'][$session->status] }}</span></div>
                    <div class="detail-facts"><div><small>{{ auth()->id() === $session->mentor_id ? 'Mentee' : 'Mentor' }}</small><b>{{ auth()->id() === $session->mentor_id ? $session->mentee->name : $session->mentor->name }}</b></div><div><small>Fecha y hora</small><b>{{ $session->starts_at->format('d/m/Y · H:i') }}</b></div><div><small>Duración</small><b>{{ $session->duration }} minutos</b></div></div>
                    @if ($session->note)<div class="session-note"><small>Tema que quieren trabajar</small><p>{{ $session->note }}</p></div>@endif
                    <div class="session-actions">
                        @if ($session->status === 'pending' && auth()->id() === $session->mentor_id)
                            <form method="POST" action="{{ route('sessions.status', [$session, 'accepted']) }}">@csrf<button class="button" type="submit">Aceptar solicitud</button></form>
                            <form method="POST" action="{{ route('sessions.status', [$session, 'cancelled']) }}">@csrf<button class="button button-danger-light" type="submit">Rechazar</button></form>
                        @elseif ($session->status === 'accepted' && auth()->id() === $session->mentor_id)
                            @if ($session->starts_at->copy()->addMinutes($session->duration)->isPast())
                                <form method="POST" action="{{ route('sessions.status', [$session, 'completed']) }}">@csrf<button class="button" type="submit">Marcar como completada</button></form>
                            @else
                                <span class="field-hint">Al terminar la sesión podrás marcarla como completada.</span>
                            @endif
                            <form method="POST" action="{{ route('sessions.status', [$session, 'cancelled']) }}">@csrf<button class="button button-danger-light" type="submit">Cancelar sesión</button></form>
                        @elseif (in_array($session->status, ['pending', 'accepted']) && auth()->id() === $session->mentee_id)
                            <form method="POST" action="{{ route('sessions.status', [$session, 'cancelled']) }}">@csrf<button class="button button-danger-light" type="submit">Cancelar solicitud</button></form>
                        @endif
                    </div>
                </article>

                @if ($session->status === 'completed' && auth()->id() === $session->mentee_id && ! $session->review)
                    <form method="POST" action="{{ route('sessions.review', $session) }}" class="panel form-panel form-stack review-form">
                        @csrf
                        <span class="eyebrow">Comparte tu experiencia</span><h2>¿Cómo fue la mentoría?</h2>
                        <label>Calificación
                            <select name="rating" required><option value="">Selecciona una calificación</option>@for ($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}">{{ $rating }} · {{ $rating === 5 ? 'Excelente' : ($rating === 1 ? 'Necesita mejorar' : 'Estuvo bien') }}</option>@endfor</select>
                        </label>
                        <label>Comentario opcional<textarea name="comment" rows="3" maxlength="1000" placeholder="¿Qué fue lo que más te ayudó?"></textarea></label>
                        <button class="button" type="submit">Enviar calificación</button>
                    </form>
                @elseif ($session->review)
                    <div class="panel review-card"><span class="eyebrow">Tu calificación</span><div class="review-head"><b>{{ $session->review->rating }} de 5 estrellas</b><span class="mentor-rating">★ {{ $session->review->rating }}.0</span></div><p>{{ $session->review->comment ?: 'Gracias por compartir tu experiencia.' }}</p></div>
                @endif
            </div>

            <aside class="panel conversation-panel" id="conversation">
                <div class="conversation-heading"><span class="eyebrow">Coordinen aquí</span><h2>Chat de la sesión</h2><p>Los mensajes quedan guardados para ambas personas.</p></div>
                <div class="message-list">
                    @forelse ($session->messages as $message)
                        <div class="message {{ $message->sender_id === auth()->id() ? 'message-own' : '' }}"><small>{{ $message->sender_id === auth()->id() ? 'Tú' : $message->sender->name }} · {{ $message->created_at->format('d/m H:i') }}</small><p>{{ $message->body }}</p></div>
                    @empty
                        <div class="message-empty"><span>✉</span><p>Aún no hay mensajes. Puedes compartir el tema que quieres trabajar o coordinar los detalles.</p></div>
                    @endforelse
                </div>
                @if ($session->status !== 'cancelled')
                    <form method="POST" action="{{ route('sessions.message', $session) }}" class="message-form">
                        @csrf
                        <label class="sr-only" for="message-body">Escribe un mensaje</label>
                        <textarea id="message-body" name="body" rows="2" maxlength="2000" placeholder="Escribe un mensaje..." required></textarea>
                        <button class="button" type="submit" aria-label="Enviar mensaje">Enviar</button>
                    </form>
                @endif
                <p class="chat-note">El chat se actualiza al recargar la conversación. La videollamada integrada no forma parte de esta primera versión.</p>
            </aside>
        </div>
    </section>
@endsection
