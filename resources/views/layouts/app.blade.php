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
            <a href="/menu" class="navbar-brand" style="display: flex; align-items: center; gap: 8px;">
                <img src="{{ asset('images/icons/logo_utama.png') }}" alt="Sego Sambelan Logo" style="height: 40px; width: auto; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(234, 88, 12, 0.2));">
                <span class="brand-text">Sego Sambelan</span>
            </a>

            <div class="navbar-menu" id="navbar-menu">
                {{-- Link Menu selalu tampil (guest & auth buyer) --}}
                @guest
                <a href="/menu" class="nav-link {{ request()->is('menu*') ? 'active' : '' }}">Menu</a>
                @endguest

                @auth
                @if(auth()->user()->isBuyer())
                <a href="/menu" class="nav-link {{ request()->is('menu*') ? 'active' : '' }}">Menu</a>
                <a href="/orders/history" class="nav-link {{ request()->is('orders*') ? 'active' : '' }}">Pesanan</a>
                <a href="/cart" class="nav-link cart-link {{ request()->is('cart*') ? 'active' : '' }}" style="display: inline-flex; align-items: center; gap: 4px;">
                    <img src="{{ asset('images/icons/icon_cart.svg') }}" alt="Keranjang" style="height: 22px; width: 22px; object-fit: contain;">
                    @php $cartCount = collect(session('cart', []))->sum('quantity'); @endphp
                    @if($cartCount > 0)
                    <span class="cart-badge" id="cart-badge">{{ $cartCount }}</span>
                    @endif
                </a>
                @endif

                @if(auth()->user()->isDriver())
                <a href="{{ route('driver.orders.index') }}" class="nav-link {{ request()->is('driver/orders*') ? 'active' : '' }}" style="display: inline-flex; align-items: center; gap: 6px;">
                    <img src="{{ asset('images/icons/logo_driver.png') }}" alt="Driver" style="height: 22px; width: auto; object-fit: contain; filter: drop-shadow(0 2px 3px rgba(0,0,0,0.15));"> Tugas Saya
                </a>
                @endif
                <div class="nav-user">
                    <span class="nav-user-name">{{ auth()->user()->name }}</span>
                    <form action="/logout" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="nav-link btn-logout">Logout</button>
                    </form>
                </div>
                @endauth

                {{-- Tombol Login & Daftar untuk tamu --}}
                @guest
                <div class="nav-guest-actions">
                    <a href="/login" class="nav-link">Masuk</a>
                    <a href="/register" class="btn btn-primary btn-sm">Daftar</a>
                </div>
                @endguest
            </div>

            <button class="hamburger" id="hamburger" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
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

    {{-- Floating Cart Button --}}
    @auth
        @if(auth()->user()->isBuyer())
            @php $cartCount = collect(session('cart', []))->sum('quantity'); @endphp
            <a href="/cart" class="floating-cart {{ $cartCount > 0 ? 'show' : '' }}" id="floating-cart" title="Keranjang Belanja">
                <img src="{{ asset('images/icons/icon_cart.svg') }}" alt="Keranjang" class="floating-cart-icon" style="height: 28px; width: 28px; object-fit: contain; filter: brightness(0) invert(1);">
                <span class="floating-cart-badge" id="floating-cart-badge">{{ $cartCount }}</span>
            </a>
        @endif
    @endauth

    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/smart-refresh.js') }}"></script>
    @stack('scripts')
</body>
</html>
