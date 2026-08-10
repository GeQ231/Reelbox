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
    <h2 class="mb-4 title">Registrazione</h2>

    <form id="registerForm" method="POST" action="{{ route('register') }}" class="color_rect" novalidate>
        @csrf

        <div class="mb-3">
            <label>Nome</label>
            <input type="text" name="name" id="name" class="form-control" required autofocus>
            <div class="error-msg" id="nameError"></div>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
            <div class="error-msg" id="emailError"></div>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
            <div class="error-msg" id="passwordError"></div>
        </div>

        <div class="mb-3">
            <label>Conferma Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            <div class="error-msg" id="passwordConfirmError"></div>
        </div>

        <button type="submit" class="btn btn-success data_submit_button">Registrati</button>
    </form>

    <p class="mt-3 title">Hai già un account? <a href="{{ route('login') }}">Accedi</a></p>
</div>

<script>
    document.getElementById('registerForm').addEventListener('submit', function(event) {
        // reset errori
        document.getElementById('nameError').textContent = '';
        document.getElementById('emailError').textContent = '';
        document.getElementById('passwordError').textContent = '';
        document.getElementById('passwordConfirmError').textContent = '';

        let valid = true;

        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        const password_confirmation = document.getElementById('password_confirmation').value.trim();

        // Controllo che email contenga almeno una '@' e almeno un '.'
        if (!name) {
            document.getElementById('nameError').textContent = 'Il nome è obbligatorio.';
            valid = false;
        }

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
        } else if (password.length < 6) {
            document.getElementById('passwordError').textContent = 'La password deve essere lunga almeno 6 caratteri.';
            valid = false;
        }

        if (!password_confirmation) {
            document.getElementById('passwordConfirmError').textContent = 'Conferma la password.';
            valid = false;
        } else if (password !== password_confirmation) {
            document.getElementById('passwordConfirmError').textContent = 'Le password non corrispondono.';
            valid = false;
        }

        if (!valid) {
            event.preventDefault(); // blocca submit
        }
    });
</script>
@endsection
