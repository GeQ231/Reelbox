@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="title">I miei preferiti</h1>

    @if($preferiti->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="bi bi-heart" style="font-size:3rem;"></i>
            <p class="mt-3">Non hai ancora aggiunto nessun preferito!</p>
            <a href="{{ route('home') }}" class="btn btn-primary mt-2">Esplora contenuti</a>
        </div>
    @else
        <div class="row g-4 mt-2">
            @foreach($preferiti as $pref)
            @if($pref->content)
            <div class="col-md-4 col-sm-6">
                <div class="color_rect p-3 rounded h-100 d-flex flex-column">
                    
                    {{-- POSTER --}}
                    @if(!empty($pref->content->poster))
                    <img src="{{ $pref->content->poster }}"
                         alt="{{ $pref->content->titolo }}"
                         style="width:100%; height:250px; object-fit:cover; border-radius:8px; margin-bottom:10px;">
                    @else
                    <div style="width:100%; height:250px; background:rgba(255,255,255,0.05); border-radius:8px; margin-bottom:10px; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-film" style="font-size:3rem; color:rgba(255,255,255,0.3);"></i>
                    </div>
                    @endif

                    {{-- INFO --}}
                    <div class="flex-grow-1">
                        <h5 class="title" style="text-align:left; font-size:1rem;">
                            {{ $pref->content->titolo }}
                        </h5>
                        <small class="text-muted">
                            {{ $pref->content->anno ?? 'N/D' }} •
                            {{ $pref->content->categoria === 'serie_tv' ? 'Serie TV' : 'Film' }}
                        </small>

                        {{-- GENERI --}}
                        @if($pref->content->tags->isNotEmpty())
                        <div class="mt-2">
                            @foreach($pref->content->tags->take(3) as $tag)
                                <span class="badge bg-secondary">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    {{-- BOTTONI --}}
                    <div class="d-flex gap-2 mt-3">
                        <a href="{{ route('films.show', $pref->content->id) }}" 
                           class="btn btn-primary btn-sm flex-grow-1">
                            <i class="bi bi-info-circle me-1"></i> Dettagli
                        </a>
                        <button class="btn btn-danger btn-sm remove-favorite" 
                                data-id="{{ $pref->content->id }}">
                            <i class="bi bi-heart-fill"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.remove-favorite').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        
        try {
            const res = await fetch(`/favorites/toggle/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const data = await res.json();

            if (!data.favorited) {
                // ✅ Rimuovi la card dalla pagina
                this.closest('.col-md-4').remove();

                // ✅ Se non ci sono più preferiti mostra messaggio
                const remaining = document.querySelectorAll('.col-md-4');
                if (remaining.length === 0) {
                    location.reload();
                }
            }

        } catch (err) {
            console.error('Errore:', err);
        }
    });
});
</script>
@endsection