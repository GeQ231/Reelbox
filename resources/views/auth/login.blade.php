@section('styles')
<link rel="stylesheet" href="{{ asset('css/General_style.css') }}">
<style>
    .error-msg {
        color: red;
        font-size: 0.9em;
        margin-top: 0.25rem;
    }
</style>
@endsection

@extends('layouts.app')
@section('content')
<div class="container">
    <h2 class="mb-4 title">Login</h2>

    <form id="loginForm" method="POST" action="{{ route('login') }}" class="color_rect" novalidate>
        @csrf

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" id="email" class="form-control" required autofocus>
            <div class="error-msg" id="emailError"></div>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
            <div class="error-msg" id="passwordError"></div>
        </div>

        <button type="submit" class="btn btn-primary data_submit_button">Accedi</button>
    </form>

    <p class="mt-3 title">Non hai un account? <a href="{{ route('register') }}">Registrati</a></p>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(event) {
    // reset errori
    document.getElementById('emailError').textContent = '';
    document.getElementById('passwordError').textContent = '';

    let valid = true;

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();

    // Controllo che email contenga almeno una '@' e almeno un '.'
    if (!email) {
        document.getElementById('emailError').textContent = 'L\'email è obbligatoria.';
        valid = false;
    } else if (email.indexOf('@') === -1 || email.indexOf('.') === -1) {
        document.getElementById('emailError').textContent = 'Inserisci un\'email valida.';
        valid = false;
    }

    if (!password) {
        document.getElementById('passwordError').textContent = 'La password è obbligatoria.';
        valid = false;
    }

    if (!valid) {
        event.preventDefault(); // blocca submit
    }
});

</script>
@endsection
