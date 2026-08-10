@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/forum.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/General_style.css') }}">
@endsection

@section('content')
<h2 class="title">Post nel genere: {{ $tag->name }}</h2>

@if(session('success'))
  <div class="success-message">{{ session('success') }}</div>
@endif

@forelse ($posts as $post)
  <div class="post mb-4" data-postid="{{ $post->id }}">
    <h4>{{ $post->titolo }}</h4>
    <p>{{ $post->contenuto }}</p>
    <small>Postato da {{ $post->user->name }}</small>
  {{-- Eliminazione di un post  --}}
  @if (Auth::check() && (Auth::id() === $post->user_id || Auth::user()->isAdmin()))
  <form method="POST" action="{{ route('forum.posts.destroy', $post) }}" style="height:0.2em;">
    @csrf
    @method('DELETE')
    <div class="flex-right">
      <button type="submit" class="button" aria-label="Elimina post">
        <i class="bi bi-trash3"></i> Elimina
  </button>
</div>

  </form>
@endif



    {{-- Sezione Like --}}
    <div class="like-section mt-2">
      @auth
        <div class="like-toggle" style="cursor:pointer;" data-postid="{{ $post->id }}">
            <i class="bi me-1 {{ $post->likes->contains('user_id', auth()->id()) ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
            <span class="like-count">{{ $post->likes->count() }}</span> Like
    </div>
      @endauth

      @guest
        <div class="text-muted">
          <i class="bi bi-heart me-1"></i> Accedi per mettere like
        </div>
      @endguest
    </div>

    {{-- Sezione Commenti --}}
    <div class="comment-section mt-3">
      <h5>Commenti ({{ $post->comments->count() }})</h5>
      <ul>
        @foreach ($post->comments as $comment)
          <li>
            <strong>{{ $comment->user->name }}:</strong> {{ $comment->comment }}
            @if (Auth::check() && (Auth::id() === $comment->user_id || Auth::user()->isAdmin()))
                <form method="POST" action="{{ route('comments.destroy', $comment->id) }}" style="display: inline; background: transparent; padding: 0; margin: 0; border: none;">
              @csrf
              @method('DELETE')
              <button type="submit" class="delete-comment-btn" aria-label="Elimina commento">&times;</button>
              </form>
          @endif
          </li>
        @endforeach
      </ul>

      {{-- Aggiungi nuovo commento --}}
      @auth
        <form method="POST" action="{{ route('posts.comments.store', $post->id) }}" class="mt-2">
          @csrf
          <textarea name="comment" placeholder="Scrivi un commento..." required></textarea>
          <button type="submit" class="comment-button">Commenta</button>
        </form>
      @endauth
    </div>
  </div>
@empty
  <p class="empty-message">Nessun post presente per questo genere.</p>
@endforelse

{{-- Nuovo post --}}
@if(Auth::check())
  <hr>
  <h3 class="title">Crea un nuovo post</h3>
  <form method="POST" action="{{ route('forum.post', $tag->id) }}" class="color_rect">
    @csrf
    <div>
      <input type="text" name="titolo" placeholder="Titolo" required>
    </div>
    <div class="mt-2">
      <textarea name="contenuto" placeholder="Contenuto" required></textarea>
    </div>
    <button type="submit" class="data_submit_button mt-2">Pubblica</button>
  </form>
@else
  <p><a href="{{ route('login') }}">Accedi</a> per pubblicare un post.</p>
@endif

@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const likeToggles = document.querySelectorAll('.like-toggle');
    const userIsLoggedIn = @json(Auth::check());

    likeToggles.forEach(toggle => {
      toggle.addEventListener('click', async () => {
        if (!userIsLoggedIn) {
          alert('Devi essere loggato per mettere like.');
          return;
        }

        const postId = toggle.dataset.postid;
        

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        

        try {
          const res = await fetch(`/like/${postId}`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json',
            }
          });

          if (!res.ok) {
            alert('Errore HTTP: ' + res.status);
            throw new Error('Errore nella richiesta');
          }

          const data = await res.json();

          const heartIcon = toggle.querySelector('i.bi');
          const countSpan = toggle.querySelector('.like-count');

          countSpan.textContent = data.likes_count;

          if (data.user_has_liked) {
            heartIcon.classList.add('bi-heart-fill', 'text-danger');
            heartIcon.classList.remove('bi-heart');
          } else {
            heartIcon.classList.remove('bi-heart-fill', 'text-danger');
            heartIcon.classList.add('bi-heart');
          }

        } catch (err) {
          console.error(err);
          alert('Errore durante il like: ' + err.message);
        }
      });
    });
  });
</script>
@endsection



