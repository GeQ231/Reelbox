@extends('layouts.app')
{{-- Estende il layout principale 'app' --}}

@section('styles')
<link rel="stylesheet" href="{{ asset('css/General_style.css') }}">
@endsection

@section('content')
<div class="container mt-4">
    {{-- Contenitore con margine superiore --}}

    <h1 class="mb-3 title">Scegli un genere</h1>
    {{-- Titolo della pagina con margine inferiore --}}

    <div class="d-flex flex-wrap gap-2">
        {{-- Contenitore flexbox che permette ai bottoni di andare a capo e avere uno spazio (gap) tra loro --}}
        
        @foreach ($tags as $tag)
            {{-- Ciclo su tutti i generi/tag passati dalla controller --}}
            <a href="{{ route('forum.show', $tag->id) }}" class="glitch-btn">
                {{-- Link stile bottone che porta alla pagina del forum per il genere specifico --}}
                {{ $tag->name }}
                {{-- Nome del genere/tag --}}
            </a>
        @endforeach
    </div>
</div>
@endsection
