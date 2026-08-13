@extends('layouts.app')

@section('content')
<div class="color_rect p-4 rounded shadow">

    <h1 class="title">{{ $content->titolo }}</h1>

    <div class="d-flex gap-4 mt-3">

        {{-- POSTER --}}
        @if(!empty($content->poster))
        <div style="flex-shrink:0;">
            <img src="{{ $content->poster }}"
                 alt="{{ $content->titolo }}"
                 style="width:200px; border-radius:10px; display:block;">
        </div>
        @endif

        {{-- INFO --}}
        <div class="flex-grow-1">
            <p><strong>Regista:</strong> {{ $content->regista ?? 'N/D' }}</p>
            <p><strong>Anno:</strong> {{ $content->anno ?? 'N/D' }}</p>

            @if($content->tags->isNotEmpty())
            <div class="mb-3">
                <strong>Generi:</strong>
                @foreach($content->tags as $tag)
                    <span class="badge bg-secondary">{{ $tag->name }}</span>
                @endforeach
            </div>
            @endif

            <div class="mt-3">
                <strong>Trama:</strong>
                <p class="mt-2">{!! nl2br(e($content->descrizione ?? 'Nessuna descrizione disponibile.')) !!}</p>
            </div>

            @auth
                <div class="mt-3">
                    <button id="favorite-button" 
                            data-content-id="{{ $content->id }}" 
                            class="btn {{ $isFavorited ? 'btn-danger' : 'btn-outline-danger' }}">
                        <i class="bi {{ $isFavorited ? 'bi-heart-fill' : 'bi-heart' }} me-1"></i>
                        <span id="favorite-text">
                            {{ $isFavorited ? 'Rimuovi dai preferiti' : 'Aggiungi ai preferiti' }}
                        </span>
                    </button>
                </div>
                @endauth
        </div>
    </div>

    {{-- TRAILER --}}
    @if(!empty($content->trailer_url))
    <div class="mt-4">
        <h4>Trailer</h4>
        <iframe 
            width="700" 
            height="400"
            src="{{ $content->trailer_url }}"
            frameborder="0"
            allowfullscreen
            style="border-radius:8px;">
        </iframe>
    </div>
    @else
        <p class="text-muted mt-3">Trailer non disponibile.</p>
    @endif

    {{-- COMMENTI --}}
    <div class="mt-5">
        <h4 class="title mb-4">Commenti</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- FORM COMMENTO --}}
        @auth
        <div class="mb-4">
            <form method="POST" action="{{ route('comments.store', $content->id) }}" 
                  style="background:transparent; border:none; padding:0; box-shadow:none;">
                @csrf
                <textarea
                    name="body"
                    rows="3"
                    placeholder="Scrivi un commento..."
                    required
                    style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.2); border-radius:8px; color:white; padding:10px; resize:vertical;">
                </textarea>
                <button type="submit" class="btn btn-primary mt-2">
                    <i class="bi bi-send me-1"></i> Commenta
                </button>
            </form>
        </div>
        @else
            <p><a href="{{ route('login') }}">Accedi</a> per commentare.</p>
        @endauth

        {{-- LISTA COMMENTI --}}
        @forelse($content->comments as $comment)
        <div style="border-bottom: 1px solid rgba(255,255,255,0.1); padding: 12px 0;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-person-circle"></i>
                        <strong>{{ $comment->user->name }}</strong>
                        <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="mb-0 ms-4">{{ $comment->body }}</p>
                </div>

                @if(Auth::check() && (Auth::id() === $comment->user_id || Auth::user()->isAdmin()))
                <form method="POST" action="{{ route('comments.destroy', $comment->id) }}"
                      style="background:transparent; border:none; padding:0; margin:0; box-shadow:none;">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            style="background:transparent; border:none; color:#dc3545; cursor:pointer; font-size:1.2rem; padding:5px;">
                        <i class="bi bi-trash3"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
            <div class="text-center text-muted py-4">
                <i class="bi bi-chat-dots" style="font-size:2rem;"></i>
                <p class="mt-2">Nessun commento ancora. Sii il primo!</p>
            </div>
        @endforelse
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const button    = document.getElementById('favorite-button');
    const contentId = button?.dataset.contentId;
    const textSpan  = document.getElementById('favorite-text');

    button?.addEventListener('click', function () {
        fetch(`/favorites/toggle/${contentId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.favorited) {
                textSpan.textContent = 'Rimuovi dai preferiti';
                button.classList.replace('btn-outline-danger', 'btn-danger');
            } else {
                textSpan.textContent = 'Aggiungi ai preferiti';
                button.classList.replace('btn-danger', 'btn-outline-danger');
            }
        })
        .catch(err => console.error('Errore:', err));
    });
});
</script>
@endsection