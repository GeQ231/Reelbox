<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>ReelBox</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('css/nav_bar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/forum.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/General_style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/user_page.css') }}?v={{ time() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}" type="image/png">

    @yield('styles')
</head>
<body>

    {{-- ✅ Scanline effect come div separato --}}
    <div style="
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: repeating-linear-gradient(
            0deg,
            transparent,
            transparent 2px,
            rgba(0, 230, 246, 0.01) 2px,
            rgba(0, 230, 246, 0.01) 4px
        );
        pointer-events: none;
        z-index: 1;
    "></div>

    {{-- NAVBAR --}}
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img class="web-logo" src="{{ asset('favicon-32x32.png') }}" alt="Logo">
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

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')

    {{-- ✅ Script DENTRO body, DOPO Bootstrap --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {

        // ✅ Fade in pagina
        document.body.style.opacity = '0';
        document.body.style.transition = 'opacity 0.5s ease';
        setTimeout(() => {
            document.body.style.opacity = '1';
        }, 100);

        // ✅ Fade out su click link - escludi modal e form
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');

                // Ignora se:
                if (!href) return;
                if (href.startsWith('#')) return;
                if (href.startsWith('javascript')) return;
                if (this.hasAttribute('data-bs-toggle')) return;
                if (this.hasAttribute('data-bs-dismiss')) return;
                if (this.closest('.modal')) return; // ✅ Dentro modal
                if (this.closest('form')) return;   // ✅ Dentro form

                e.preventDefault();
                document.body.style.opacity = '0';
                setTimeout(() => {
                    window.location.href = href;
                }, 400);
            });
        });

        // ✅ Parallax sfondo
        document.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth - 0.5) * 20;
            const y = (e.clientY / window.innerHeight - 0.5) * 20;
            document.body.style.backgroundPosition = 
                `${50 + x * 0.1}% ${50 + y * 0.1}%`;
        });

    }); // ✅ Chiusura corretta DOMContentLoaded
    </script>

</body>
</html>