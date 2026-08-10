@extends('layouts.app')

<!-- foglio di stile per le singole pagine dei film  -->


@section('content')
<div class="color_rect p-4 rounded shadow">
    <h1 class="title">{{ $content->titolo }}</h1>

    <!-- 1. CAMBIATO: da $content->trama a $content->descrizione -->
    <p><strong>Trama:</strong> {!! nl2br(e($content->descrizione ?? 'Nessuna descrizione disponibile.')) !!}</p>

    <!-- 2. AGGIUNTO: questo spazio serve per mostrare il poster (se lo trovi) -->
    @if(!empty($content->poster))
        <img src="{{ $content->poster }}" alt="{{ $content->titolo }}" style="max-width: 300px; display: block; margin-bottom: 15px;">
    @endif

    <p><strong>Regista:</strong> {{ $content->regista }}</p>
    <p><strong>Anno:</strong> {{ $content->anno }}</p>

    <!-- Il resto del codice che avevi rimane uguale da qui in poi... -->
    @if($content->tags->isNotEmpty())
    <div class="mb-3">
        <strong>Genres:</strong>
        @foreach($content->tags as $tag)
            <span class="badge bg-secondary">{{ $tag->name }}</span>
        @endforeach
    </div>
    @endif

    
</div>

@endsection

@section('scripts')

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const button = document.getElementById('favorite-button');
        const contentId = button?.dataset.contentId;
        const textSpan = document.getElementById('favorite-text');

        button?.addEventListener('click', function () {
            fetch(`/favorites/toggle/${contentId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.favorited === true) {
                    textSpan.textContent = 'Rimuovi dai preferiti';
                } else if (data.favorited === false) {
                    textSpan.textContent = 'Aggiungi ai preferiti';
                }
            })
            .catch(error => {
                console.error('Errore nel toggle preferiti:', error);
            });
        });
    });
    </script>
@endsection

