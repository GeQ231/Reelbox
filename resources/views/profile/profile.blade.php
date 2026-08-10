@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/user_page.css') }}">
<link rel="stylesheet" href="{{ asset('css/General_style.css') }}">
@endsection

@section('content')
<div class="profile-container color_rect ">
    <h1 class="profile-title title">Profilo Utente</h1>

    <div class="profile-info">
        <p><strong>Nome:</strong> {{ $user->name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
    </div>

    <div class="preferences-section">
        <button class="preferences-toggle data_search_button " onclick="togglePreferences()" aria-expanded="false" aria-controls="preferencesList">
            Preferenze
            <i id="arrowIcon" class="bi bi-chevron-down"></i>
        </button>

        <div id="preferencesList" class="preferences-list hidden">
            @if(!is_null($preferences) && $preferences->count())
                <ul>
                    @foreach($preferences as $pref)
                        <li>{{ $pref->name ?? 'Tag non disponibile' }}</li>
                    @endforeach
                </ul>
            @else
                <p class="no-preferences">Nessuna preferenza salvata.</p>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('logout') }}" style="background-color:#2d2d44;" >
        @csrf
        <button type="submit" class="logout-btn data_delete_button">Logout</button>
    </form>
</div>
@endsection

<script>
function togglePreferences() {
    const list = document.getElementById('preferencesList');
    const arrow = document.getElementById('arrowIcon');
    list.classList.toggle('hidden');

    const expanded = arrow.parentElement.getAttribute('aria-expanded') === 'true';
    arrow.parentElement.setAttribute('aria-expanded', !expanded);

    arrow.classList.toggle('rotate');
}
</script>
