<!DOCTYPE html>
<html lang="it">
<head>


 

    <link rel="stylesheet" href="{{ asset('css/nav_bar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/forum.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="{{ asset('css/General_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user_page.css') }}">

  
    <meta charset="UTF-8">
    <title>ReelBox</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap per le icone --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}" type="image/png">



    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('styles') 
</head>
<body>

  {{-- NAVBAR --}}
  <nav class="navbar navbar-expand-lg navbar-dark">
  <a class="navbar-brand d-flex align-items-center" href="/">
    <img  class="web-logo"src="{{ asset('favicon-32x32.png') }}" alt="Logo" style="">
      <span class="brand-text">ReelBox</span>
    </a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        @auth
          <li class="nav-item"><a class="nav-link" href="{{ route('profile') }}">Profilo</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('forum.index') }}">Forum</a></li>
        @else
          <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
        @endauth
      </ul>
    </div>
  </nav>

  {{-- CONTENUTO DELLA PAGINA --}}
  <div class="container mt-4">
    @yield('content')
  </div>

  {{-- Bootstrap JS bundle --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  @yield('scripts')
</body>
</html>
