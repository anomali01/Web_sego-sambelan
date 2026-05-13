<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sego Sambelan')</title>
    <meta name="description" content="Sego Sambelan - Warung makan khas sambal Nusantara. Pesan online untuk delivery atau dine-in.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    {{-- Navbar --}}
    <nav class="navbar">
        <div class="container navbar-inner">
            <a href="{{ auth()->check() && auth()->user()->isSeller() ? '/admin/dashboard' : '/menu' }}" class="navbar-brand">
                <span class="brand-icon">🔥</span>
                <span class="brand-text">Sego Sambelan</span>
            </a>

            @auth
            <div class="navbar-menu">
                @if(auth()->user()->isBuyer())
                <a href="/menu" class="nav-link {{ request()->is('menu*') ? 'active' : '' }}">Menu</a>
                <a href="/orders/history" class="nav-link {{ request()->is('orders*') ? 'active' : '' }}">Pesanan</a>
                <a href="/cart" class="nav-link cart-link {{ request()->is('cart*') ? 'active' : '' }}">
                    🛒
                    @php $cartCount = collect(session('cart', []))->sum('quantity'); @endphp
                    @if($cartCount > 0)
                    <span class="cart-badge" id="cart-badge">{{ $cartCount }}</span>
                    @endif
                </a>
                @endif
                <div class="nav-user">
                    <span class="nav-user-name">{{ auth()->user()->name }}</span>
                    <form action="/logout" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="nav-link btn-logout">Logout</button>
                    </form>
                </div>
            </div>
            <button class="hamburger" id="hamburger" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
            @endauth
        </div>
    </nav>

    {{-- Flash Messages --}}
    <div class="container">
        @foreach(['success', 'error', 'warning', 'info'] as $type)
            @if(session($type))
            <div class="alert alert-{{ $type }}" id="flash-alert">
                {{ session($type) }}
                <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
            @endif
        @endforeach
    </div>

    {{-- Content --}}
    <main class="main-content">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="footer">
        <div class="container footer-inner">
            <p>&copy; {{ date('Y') }} Sego Sambelan. Semua hak dilindungi.</p>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
