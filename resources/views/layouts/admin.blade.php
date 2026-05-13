<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Sego Sambelan Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        {{-- Sidebar --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="brand-icon">🔥</span>
                <span class="brand-text">Sego Sambelan</span>
            </div>
            <nav class="sidebar-nav">
                <a href="/admin/dashboard" class="sidebar-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <span class="sidebar-icon">📊</span> Dashboard
                </a>
                <a href="/admin/products" class="sidebar-link {{ request()->is('admin/products*') ? 'active' : '' }}">
                    <span class="sidebar-icon">🍽️</span> Menu
                </a>
                <a href="/admin/orders" class="sidebar-link {{ request()->is('admin/orders*') ? 'active' : '' }}">
                    <span class="sidebar-icon">📦</span> Pesanan
                    @php $pendingCount = \App\Models\Order::where('status','pending')->count(); @endphp
                    @if($pendingCount > 0)
                    <span class="sidebar-badge">{{ $pendingCount }}</span>
                    @endif
                </a>
            </nav>
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <span>👤 {{ auth()->user()->name }}</span>
                </div>
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="sidebar-link logout-link">
                        <span class="sidebar-icon">🚪</span> Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="admin-main">
            <header class="admin-topbar">
                <button class="hamburger" id="admin-hamburger" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
                <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
            </header>

            <div class="admin-content">
                @foreach(['success', 'error', 'warning', 'info'] as $type)
                    @if(session($type))
                    <div class="alert alert-{{ $type }}">
                        {{ session($type) }}
                        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                    @endif
                @endforeach

                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
