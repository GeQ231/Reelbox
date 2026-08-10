@extends('layouts.app')

@section('content')

{{-- ricerca --}}
<div class="container mt-4">
  <h1 class="title">Esplora Contenuti</h1>
  
  <form method="GET" action="{{ route('home') }}" class="mb-4 color_rect">
    <div class="d-flex gap-3 align-items-center">
      <input type="text" name="query" class="form-control flex-grow-1 search_zone_item" placeholder="Cerca film o serie...">
      <button type="submit" class="btn data_search_button search_zone_item" style="width: 40px; min-width: 40px;">
        <i class="bi bi-search"></i>
      </button>
      <button type="button" class="btn data_search_button search_zone_item" style="width: 40px; min-width: 40px;" data-bs-toggle="collapse" data-bs-target="#filtroCollapse">
        <i class="bi bi-funnel"></i>
      </button>
    </div>

    <div class="collapse mt-3 border p-3 rounded" id="filtroCollapse">
      <div class="row g-2">
        <div class="col-md-4">
          <label for="tipologia" class="form-label">Tipologia</label>
          <select name="tipologia" id="tipologia" class="form-select">
            <option value="">Tutti</option>
            <option value="serie_tv">Serie TV</option>
            <option value="film">Film</option>
          </select>
        </div>
        <div class="col-md-4">
          <label for="anno" class="form-label">Anno</label>
          <input type="number" name="anno" id="anno" class="form-control" placeholder="Es: 2024">
        </div>
        <div class="col-md-4">
          <label for="genere" class="form-label">Genere</label>
          <select name="genere" id="genere" class="form-select">
            <option value="">Tutti</option>
          </select>
        </div>
      </div>
    </div>
  </form>

  {{-- carta del film --}}
  <div id="film-card" class="card w-100 h-100 position-relative overflow-hidden">
    <div id="film-card-bg" aria-hidden="true" style="background-image: none"></div>
  
    <div id="film-card-content" class="d-flex w-100 align-items-stretch">
    
      <div class="d-flex flex-column me-3" style="width: 200px;">
        <img id="film-immagine" src="" alt="Copertina film" class="img-fluid mb-3" style="display:none;">
      
        @auth
          <div id="film-like" class="mb-2" style="cursor:pointer;" data-filmid="">
            <i class="bi me-1 bi-heart"></i>
            <span id="like-count">0</span> Like
          </div>
        @endauth

        @guest
          <div class="mb-2 text-muted">
            <i class="bi bi-heart me-1" style="color:white;"></i>
            <span style="color:white;">Accedi per like</span>
          </div>
        @endguest
      
        <div id="film-commenti" style="cursor:pointer;">
          <i class="bi bi-chat me-1"></i>
          <span id="comment-count">0</span> Commenti
        </div>
      </div>
    
      <div class="flex-grow-1">
        <h4 id="film-titolo">Caricamento...</h4>
        <p id="film-descrizione">Attendere mentre carichiamo un film...</p>
        <a href="#" id="film-details-button" class="btn btn-outline-primary mt-3">
          Vai alla pagina del film 
        </a>
      </div>

    </div>
  </div>

  {{-- caricamento --}}
  <div id="loading" class="text-center mt-3" style="display: none;">
    <div class="spinner-border text-primary" role="status"></div>
  </div>

  {{-- Bottone per successivi content --}}
  <div class="text-center mt-3">
    <button id="nextBtn" class="btn btn-primary">Carica un altro film</button>
  </div>

  {{-- Modal commenti --}}
  <div class="modal fade" id="commentiModal" tabindex="-1" aria-labelledby="commentiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header color_rect">
          <h5 class="modal-title title" id="commentiModalLabel">Commenti</h5>
          <button type="button" class="btn-close data_delete_button" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="modal-body color_rect">
          <div id="commenti-container">Caricamento commenti...</div>
        </div>
        <div class="modal-footer color_rect">
          @auth
            <form id="comment-form" class="d-flex w-100 gap-2">
              <input type="text" class="form-control" id="nuovo-commento" placeholder="Scrivi un commento..." required>
              <button type="submit" class="btn btn-primary">Invia</button>
            </form>
          @else
            <span class="text-muted">Accedi per scrivere un commento.</span>
          @endauth
        </div>
      </div>
    </div>
  </div>

</div>

@endsection

@section('scripts')
<script>

// Variabili globali
const likeDiv = document.getElementById('film-like');
const likeCount = document.getElementById('like-count');
const commentiBtn = document.getElementById('film-commenti');
const commentiContainer = document.getElementById('commenti-container');
const commentForm = document.getElementById('comment-form');
const nuovoCommento = document.getElementById('nuovo-commento');

let filmId = null;
let commentiModal = null;

// Info utente dal PHP
const userIsLoggedIn = @json(Auth::check());
const userId = @json(Auth::id());
const userIsAdmin = @json(Auth::user()?->is_admin ?? false);

// Inizializza modal Bootstrap
document.addEventListener('DOMContentLoaded', () => {
    commentiModal = new bootstrap.Modal(document.getElementById('commentiModal'));
});

// ============================================
// G E S T I O N E   L I K E
// ============================================
if (likeDiv) {
    likeDiv.addEventListener('click', async () => {
        if (!userIsLoggedIn) {
            alert("Devi essere loggato per mettere like.");
            return;
        }
        if (!filmId) {
            alert("Film non caricato correttamente.");
            return;
        }

        try {
            const res = await fetch(`/like/${filmId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            });

            if (!res.ok) throw new Error("Errore nel mettere like.");

            const data = await res.json();
            likeCount.textContent = data.like_count;

            const heartIcon = likeDiv.querySelector('i.bi');
            if (data.user_has_liked) {
                heartIcon.classList.add('bi-heart-fill', 'text-danger');
                heartIcon.classList.remove('bi-heart');
            } else {
                heartIcon.classList.remove('bi-heart-fill', 'text-danger');
                heartIcon.classList.add('bi-heart');
            }

        } catch (err) {
            console.error('Errore like:', err);
            alert("Errore nel mettere like.");
        }
    });
}

// ============================================
// C A R I C A M E N T O   F I L M
// ============================================
document.addEventListener('DOMContentLoaded', () => {

    const titolo       = document.getElementById('film-titolo');
    const descrizione  = document.getElementById('film-descrizione');
    const loading      = document.getElementById('loading');
    const nextBtn      = document.getElementById('nextBtn');
    const immagine     = document.getElementById('film-immagine');
    const filmCardBg   = document.getElementById('film-card-bg');
    const form         = document.querySelector('form');

    async function caricaFilm() {
        loading.style.display = 'block';
        nextBtn.disabled = true;

        try {
            const formData = new FormData(form);
            const params = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }

            if (userIsLoggedIn && userId) {
                params.append('user_id', userId);
            }

            const url = `{{ url('/search') }}?${params.toString()}`;
            const res = await fetch(url);

            if (!res.ok) throw new Error('Errore durante il caricamento');

            const film = await res.json();

            // Salvo ID film
            filmId = film.id;
            if (likeDiv) likeDiv.dataset.filmid = filmId;

            // Bottone pagina film
            const detailsButton = document.getElementById('film-details-button');
            if (detailsButton && film.id) {
                detailsButton.href = `/contents/${film.id}`;
                detailsButton.style.display = 'inline-block';
            }

            // Aggiorno like
            if (likeDiv) {
                likeCount.textContent = film.likes_count || 0;
                const heartIcon = likeDiv.querySelector('i.bi');
                if (film.user_has_liked) {
                    heartIcon.classList.add('bi-heart-fill', 'text-danger');
                    heartIcon.classList.remove('bi-heart');
                } else {
                    heartIcon.classList.remove('bi-heart-fill', 'text-danger');
                    heartIcon.classList.add('bi-heart');
                }
            }

            // Testo
            titolo.textContent     = film.titolo || 'Titolo non disponibile';
            descrizione.textContent = film.trama || 'Nessuna trama disponibile.';

            // Immagine
            if (film.image) {
                immagine.src = film.image;
                immagine.style.display = 'block';
                filmCardBg.style.backgroundImage = `url('${film.image}')`;
            } else {
                immagine.style.display = 'none';
                filmCardBg.style.backgroundImage = '';
            }

            // Conteggio commenti
            try {
                const commentRes = await fetch(`/comments/${filmId}`);
                if (!commentRes.ok) throw new Error('Errore caricamento commenti');
                const commentData = await commentRes.json();
                document.getElementById('comment-count').textContent = commentData.length;
            } catch (err) {
                console.error('Errore caricamento commenti:', err);
                document.getElementById('comment-count').textContent = '0';
            }

        } catch (error) {
            console.error('Errore:', error);
            titolo.textContent     = 'Errore';
            descrizione.textContent = 'Impossibile caricare il film.';
        } finally {
            loading.style.display = 'none';
            nextBtn.disabled = false;
        }
    }

    // Carica film all'avvio
    caricaFilm();

    // Bottone prossimo film
    nextBtn.addEventListener('click', () => caricaFilm());

    // Submit form ricerca
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        caricaFilm();
    });

    // Tags per select genere
    async function caricaTags() {
        try {
            const res = await fetch('/api/tags');
            if (!res.ok) throw new Error('Errore nel caricamento dei tag');
            const tags = await res.json();

            const selectGenere = document.getElementById('genere');
            selectGenere.length = 1;

            tags.forEach(tag => {
                const option = document.createElement('option');
                option.value = tag.id;
                option.textContent = tag.name;
                selectGenere.appendChild(option);
            });
        } catch (err) {
            console.error('Errore tags:', err);
        }
    }

    caricaTags();
});

// ============================================
// G E S T I O N E   C O M M E N T I
// ============================================

// ✅ FIX - unico listener per i commenti (rimosso il duplicato!)
async function caricaCommenti() {
    if (!filmId) return;

    commentiContainer.textContent = "Caricamento commenti...";
    commentiModal.show();

    try {
        const res = await fetch(`/comments/${filmId}`);
        if (!res.ok) throw new Error("Errore nel caricamento commenti");
        const data = await res.json();

        // Aggiorna contatore
        document.getElementById('comment-count').textContent = data.length;

        if (data.length === 0) {
            commentiContainer.innerHTML = '<p class="text-white">Nessun commento.</p>';
            return;
        }

        // ✅ FIX BUG 8 - 'commenti' → 'data' + innerHTML assegnato!
        const html = data.map(commento => `
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <div>
                    <strong>${commento.user_name}:</strong> ${commento.body}
                </div>
                ${userIsLoggedIn && userIsAdmin ?
                    `<button class="btn btn-sm btn-danger ms-3 delete-comment-btn" 
                        data-id="${commento.id}">Elimina</button>`
                    : ''
                }
            </div>
        `).join('');

        // ✅ FIX - HTML finalmente inserito nella pagina!
        commentiContainer.innerHTML = html;

        // Listener per eliminazione commenti
        document.querySelectorAll('.delete-comment-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const commentId = e.target.dataset.id;
                if (confirm('Sei sicuro di voler eliminare questo commento?')) {
                    try {
                        const res = await fetch(`/admin/comments/${commentId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                        });
                        if (!res.ok) throw new Error('Errore nella cancellazione');
                        await caricaCommenti();
                    } catch (err) {
                        alert('Errore nella cancellazione del commento.');
                        console.error(err);
                    }
                }
            });
        });

    } catch (err) {
        commentiContainer.textContent = "Errore nel caricamento commenti.";
        console.error(err);
    }
}

// Apri modal commenti
if (commentiBtn) {
    commentiBtn.addEventListener('click', caricaCommenti);
}

// Invia nuovo commento
if (commentForm) {
    commentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!filmId) return;

        const testo = nuovoCommento.value.trim();
        if (!testo) return;

        try {
            const res = await fetch(`/comments/${filmId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ body: testo })
            });

            if (!res.ok) throw new Error("Errore nell'invio commento");

            nuovoCommento.value = '';
            // ✅ Ricarica commenti dopo invio
            await caricaCommenti();

        } catch (err) {
            alert("Errore nell'invio commento");
            console.error(err);
        }
    });
}

</script>
@endsection