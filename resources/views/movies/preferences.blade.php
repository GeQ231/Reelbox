@extends('layouts.app')

@section('content')
<div class="container mt-4">
  <h2>Preferenze</h2>
  <form method="POST" action="{{ route('preferences.update') }}">
    @csrf

    <div class="form-group">
      <label>Categorie preferite</label><br>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="preferenze[]" value="film"
               {{ in_array('film', $user->preferenze ?? []) ? 'checked' : '' }}>
        <label class="form-check-label">Film</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="preferenze[]" value="tv"
               {{ in_array('tv', $user->preferenze ?? []) ? 'checked' : '' }}>
        <label class="form-check-label">Serie TV</label>
      </div>
    </div>

    <button type="submit" class="btn btn-primary mt-3">Salva</button>
  </form>
</div>
@endsection
