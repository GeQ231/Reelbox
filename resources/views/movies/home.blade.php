@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="title">Esplora Contenuti</h1>

    {{-- RICERCA --}}
    <form method="GET" action="{{ route('home') }}" class="mb-4 color_rect" id="search-form">
        <div class="d-flex gap-3 align-items-center">
            <input type="text" name="query" class="form-control flex-grow-1 search_zone_item" placeholder="Cerca film o serie...">
            <button type="submit" class="btn data_search_button search_zone_item">
                <i class="bi bi-search"></i>
            </button>
            <button type="button" class="btn data_search_button search_zone_item" data-bs-toggle="collapse" data-bs-target="#filtroCollapse">
                <i class="bi bi-funnel"></i>
            </button>
        </div>

        <div class="collapse mt-3 border p-3 rounded" id="filtroCollapse">
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Tipologia</label>
                    <select name="tipologia" class="form-select">
                        <option value="">Tutti</option>
                        <option value="serie_tv">Serie TV</option>
                        <option value="film">Film</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Anno</label>
                    <input type="number" name="anno" class="form-control" placeholder="Es: 2024">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Genere</label>
                    <select name="genere" id="genere" class="form-select">
                        <option value="">Tutti</option>
                    </select>
                </div>
            </div>
        </div>
    </form>

    {{-- CARD FILM --}}
    <div id="film-card" class="card w-100 position-relative overflow-hidden">
        <div id="film-card-bg" aria-hidden="true"></div>

        <div id="film-card-content" class="d-flex w-100 align-items-stretch p-3">

            <div class="d-flex flex-column me-3" style="width:200px; min-width:200px;">
                <img id="film-immagine" src="" alt="Copertina film" class="img-fluid mb-3 rounded" style="display:none;">

                @auth
                <div id="film-like" class="mb-2 d-flex align-items-center gap-1" style="cursor:pointer;" data-filmid="">
                    <i class="bi bi-heart icon-md"></i>
                    <span id="like-count">0</span> Like
                </div>
                @endauth

                @guest
                <div class="mb-2 text-muted d-flex align-items-center gap-1">
                    <i class="bi bi-heart"></i>
                    <span>Accedi per like</span>
                </div>
                @endguest

                <div id="film-commenti" class="d-flex align-items-center gap-1" style="cursor:pointer;">
                    <i class="bi bi-chat icon-md"></i>
                    <span id="comment-count">0</span> Commenti
                </div>
            </div>

            <div class="flex-grow-1">
                <h4 id="film-titolo">Caricamento...</h4>
                <p id="film-descrizione" class="mt-2">Attendere...</p>
                <a href="#" id="film-details-button" class="btn btn-outline-primary mt-3" style="display:none;">
                    <i class="bi bi-info-circle me-1"></i> Vai alla pagina del film
                </a>
            </div>
        </div>
    </div>

    {{-- LOADING --}}
    <div id="loading" class="text-center mt-3" style="display:none;">
        <div class="spinner-border text-primary" role="status"></div>
    </div>

    {{-- BOTTONE PROSSIMO FILM --}}
    <div class="text-center mt-3">
        <button id="nextBtn" class="btn btn-primary">
            <i class="bi bi-shuffle me-1"></i> Carica un altro film
        </button>
    </div>

</div> {{-- ✅ CHIUDI il container qui --}}

{{-- ✅ MODAL FUORI dal container --}}
<div class="modal fade" id="commentiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title title">
                    <i class="bi bi-chat-dots me-2"></i>Commenti
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="commenti-container">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer">
                @auth
                <form id="comment-form" class="d-flex w-100 gap-2">
                    <input type="text"
                           class="form-control"
                           id="nuovo-commento"
                           placeholder="Scrivi un commento..."
                           required>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i>
                    </button>
                </form>
                @else
                    <span class="text-muted">
                        <a href="{{ route('login') }}">Accedi</a> per scrivere un commento.
                    </span>
                @endauth
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const likeDiv       = document.getElementById('film-like');
const likeCount     = document.getElementById('like-count');
const commentiBtn   = document.getElementById('film-commenti');
const commentForm   = document.getElementById('comment-form');
const nuovoCommento = document.getElementById('nuovo-commento');

let filmId        = null;
let commentiModal = null;

const userIsLoggedIn = @json(Auth::check());
const userId         = @json(Auth::id());
const userIsAdmin    = @json(Auth::user()?->is_admin ?? false);

document.addEventListener('DOMContentLoaded', () => {
    commentiModal = new bootstrap.Modal(document.getElementById('commentiModal'));
});

// ============ LIKE ============
if (likeDiv) {
    likeDiv.addEventListener('click', async () => {
        if (!userIsLoggedIn) { alert("Devi essere loggato per mettere like."); return; }
        if (!filmId) { alert("Film non caricato."); return; }

        try {
            const res  = await fetch(`/contents/${filmId}/like`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            });
            const data = await res.json();
            likeCount.textContent = data.like_count;

            const heartIcon = likeDiv.querySelector('i.bi');
            if (data.user_has_liked) {
                heartIcon.classList.replace('bi-heart', 'bi-heart-fill');
                heartIcon.classList.add('text-danger');
            } else {
                heartIcon.classList.replace('bi-heart-fill', 'bi-heart');
                heartIcon.classList.remove('text-danger');
            }
        } catch (err) {
            console.error('Errore like:', err);
        }
    });
}

// ============ CARICAMENTO FILM ============
document.addEventListener('DOMContentLoaded', () => {
    const titolo      = document.getElementById('film-titolo');
    const descrizione = document.getElementById('film-descrizione');
    const loading     = document.getElementById('loading');
    const nextBtn     = document.getElementById('nextBtn');
    const immagine    = document.getElementById('film-immagine');
    const filmCardBg  = document.getElementById('film-card-bg');
    const form        = document.getElementById('search-form');
    const detailsBtn  = document.getElementById('film-details-button');

    async function caricaFilm() {
        loading.style.display = 'block';
        nextBtn.disabled      = true;

        try {
            const formData = new FormData(form);
            const params   = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }
            if (userIsLoggedIn && userId) params.append('user_id', userId);

            const res = await fetch(`{{ url('/search') }}?${params.toString()}`);
            
            // ✅ Gestione 404 - nessun risultato
            if (res.status === 404) {
                const errData = await res.json();
                titolo.textContent      = '🔍 Nessun risultato';
                descrizione.textContent = errData.error || 'Nessun film trovato con questi filtri. Prova a cambiare i parametri!';
                immagine.style.display           = 'none';
                filmCardBg.style.backgroundImage = '';
                detailsBtn.style.display         = 'none';
                document.getElementById('comment-count').textContent = '0';
                return;
            }

            if (!res.ok) throw new Error('Errore nel caricamento');
            
            const film = await res.json();

            filmId = film.id;
            if (likeDiv) likeDiv.dataset.filmid = filmId;

            if (detailsBtn) {
                detailsBtn.href          = `/contents/${film.id}`;
                detailsBtn.style.display = 'inline-block';
            }

            if (likeDiv) {
                likeCount.textContent = film.likes_count || 0;
                const heartIcon = likeDiv.querySelector('i.bi');
                if (film.user_has_liked) {
                    heartIcon.classList.replace('bi-heart', 'bi-heart-fill');
                    heartIcon.classList.add('text-danger');
                } else {
                    heartIcon.classList.replace('bi-heart-fill', 'bi-heart');
                    heartIcon.classList.remove('text-danger');
                }
            }

            titolo.textContent      = film.titolo || 'Titolo non disponibile';
            descrizione.textContent = film.trama  || 'Nessuna trama disponibile.';

            if (film.image) {
                immagine.src                     = film.image;
                immagine.style.display           = 'block';
                filmCardBg.style.backgroundImage = `url('${film.image}')`;
            } else {
                immagine.style.display           = 'none';
                filmCardBg.style.backgroundImage = '';
            }

            try {
                const cRes  = await fetch(`/comments/${filmId}`);
                const cData = await cRes.json();
                document.getElementById('comment-count').textContent = cData.length;
            } catch {
                document.getElementById('comment-count').textContent = '0';
            }

        } catch (error) {
            console.error('Errore:', error);
            titolo.textContent      = 'Errore';
            descrizione.textContent = 'Impossibile caricare il film. Riprova!';
            immagine.style.display           = 'none';
            filmCardBg.style.backgroundImage = '';
        } finally {
            loading.style.display = 'none';
            nextBtn.disabled      = false;
        }
    }

    caricaFilm();
    nextBtn.addEventListener('click', () => caricaFilm());
    form.addEventListener('submit', (e) => { e.preventDefault(); caricaFilm(); });

    async function caricaTags() {
        try {
            const res  = await fetch('/api/tags');
            const tags = await res.json();
            const sel  = document.getElementById('genere');
            sel.length = 1;
            tags.forEach(tag => {
                const opt       = document.createElement('option');
                opt.value       = tag.id;
                opt.textContent = tag.name;
                sel.appendChild(opt);
            });
        } catch (err) {
            console.error('Errore tags:', err);
        }
    }
    caricaTags();
});

// ============ COMMENTI ============
async function caricaCommenti() {
    if (!filmId) return;

    const container = document.getElementById('commenti-container');
    container.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
        </div>`;

    commentiModal.show();

    try {
        const res  = await fetch(`/comments/${filmId}`);
        const data = await res.json();

        document.getElementById('comment-count').textContent = data.length;

        if (data.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-chat-dots icon-lg"></i>
                    <p class="mt-2">Nessun commento ancora.</p>
                </div>`;
            return;
        }

        container.innerHTML = data.map(c => `
            <div class="comment-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-person-circle"></i>
                            <strong>${c.user_name}</strong>
                            <small class="text-muted">${c.created_at}</small>
                        </div>
                        <p class="mb-0 ms-4">${c.body}</p>
                    </div>
                    ${(userIsLoggedIn && userIsAdmin) ? `
                        <button class="comment-delete-btn delete-comment-btn" data-id="${c.id}">
                            <i class="bi bi-trash3"></i>
                        </button>` : ''}
                </div>
            </div>
        `).join('');

        document.querySelectorAll('.delete-comment-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                if (!confirm('Eliminare questo commento?')) return;
                const id = e.currentTarget.dataset.id;
                try {
                    await fetch(`/admin/comments/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        }
                    });
                    await caricaCommenti();
                } catch (err) {
                    alert('Errore nella cancellazione.');
                }
            });
        });

    } catch (err) {
        container.innerHTML = '<p class="text-danger">Errore nel caricamento commenti.</p>';
    }
}

if (commentiBtn) commentiBtn.addEventListener('click', caricaCommenti);

if (commentForm) {
    commentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!filmId) return;

        const testo = nuovoCommento.value.trim();
        if (!testo) return;

        try {
            await fetch(`/comments/${filmId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ body: testo })
            });
            nuovoCommento.value = '';
            await caricaCommenti();
        } catch (err) {
            alert("Errore nell'invio commento.");
        }
    });
}
</script>
@endsection