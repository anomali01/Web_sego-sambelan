@extends('layouts.app')
@section('title', 'Menu - Sego Sambelan')

@section('content')

{{-- Modal Login Prompt (muncul ketika guest klik Pesan) --}}
@guest
<div id="login-prompt-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-card glass-card">
        <span class="modal-icon">🔒</span>
        <h2>Login untuk Memesan</h2>
        <p>Silakan masuk atau buat akun untuk menambahkan menu ke keranjang dan melanjutkan pembelian.</p>
        <a href="/login" class="btn btn-primary btn-full">Masuk Sekarang</a>
        <a href="/register" class="btn btn-outline btn-full" style="margin-top:0.5rem">Buat Akun Baru</a>
        <button class="btn btn-sm" style="margin-top:0.8rem;color:var(--text-secondary);background:none;"
                onclick="document.getElementById('login-prompt-overlay').style.display='none'">
            Batal
        </button>
    </div>
</div>
@endguest

<div class="container py-2">
    <div class="menu-hero">
        <h1>Menu Sego Sambelan 🔥</h1>
        <p>Pilih menu favoritmu dan pesan sekarang!</p>
    </div>

    {{-- Category Tabs --}}
    <div class="category-tabs">
        <a href="/menu" class="tab {{ !request('category') ? 'active' : '' }}">🍽️ Semua</a>
        <a href="/menu?category=food" class="tab {{ request('category') === 'food' ? 'active' : '' }}">🍛 Makanan</a>
        <a href="/menu?category=drink" class="tab {{ request('category') === 'drink' ? 'active' : '' }}">🥤 Minuman</a>
    </div>

    {{-- Products Grid --}}
    <div class="products-grid">
        @forelse($products as $product)
        <div class="product-card glass-card" id="product-{{ $product->id }}">
            <div class="product-image-wrap">
                @if($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-image" loading="lazy">
                @else
                <div class="product-image-placeholder">
                    {{ $product->category === 'food' ? '🍛' : '🥤' }}
                </div>
                @endif
                <span class="product-category-badge badge-{{ $product->category }}">
                    {{ $product->category === 'food' ? 'Makanan' : 'Minuman' }}
                </span>
            </div>
            <div class="product-info">
                <h3 class="product-name">{{ $product->name }}</h3>
                <p class="product-desc">{{ Str::limit($product->description, 60) }}</p>
                <div class="product-footer">
                    <span class="product-price">{{ $product->formatted_price }}</span>
                    @if($product->isInStock())
                    <button class="btn btn-primary btn-sm btn-add-cart"
                            onclick="addToCart({{ $product->id }}, this)"
                            data-product-id="{{ $product->id }}"
                            data-requires-auth="{{ auth()->check() ? 'false' : 'true' }}">
                        🛒 Pesan
                    </button>
                    @else
                    <span class="badge badge-danger">Habis</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <span class="empty-icon">🍽️</span>
            <p>Belum ada menu tersedia saat ini.</p>
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
function addToCart(productId, btn) {
    if (!btn) btn = document.querySelector(`[data-product-id="${productId}"]`);
    const requiresAuth = btn.dataset.requiresAuth === 'true';

    // Jika guest, langsung arahkan ke halaman login
    if (requiresAuth) {
        showLoginPrompt();
        return;
    }

    const originalText = btn.innerHTML;
    btn.innerHTML = '✓ Ditambahkan';
    btn.disabled = true;
    btn.classList.add('btn-success');

    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ product_id: productId, quantity: 1 })
    })
    .then(r => {
        if (r.status === 401) return r.json().then(d => { throw { requireLogin: true, redirect: d.redirect }; });
        return r.json();
    })
    .then(data => {
        if (data.success) {
            updateCartBadge(data.cart_count);
            showToast(data.message || 'Ditambahkan ke keranjang!');
        }
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            btn.classList.remove('btn-success');
        }, 1200);
    })
    .catch(err => {
        if (err.requireLogin) {
            showLoginPrompt();
        } else {
            btn.innerHTML = originalText;
            btn.disabled = false;
            btn.classList.remove('btn-success');
        }
    });
}

function showLoginPrompt() {
    // Tampilkan modal atau redirect ke login
    const overlay = document.getElementById('login-prompt-overlay');
    if (overlay) {
        overlay.style.display = 'flex';
    } else {
        window.location.href = '/login?redirect=/menu';
    }
}

function updateCartBadge(count) {
    let badge = document.getElementById('cart-badge');
    if (!badge) {
        const cartLink = document.querySelector('.cart-link');
        if (cartLink) {
            badge = document.createElement('span');
            badge.className = 'cart-badge';
            badge.id = 'cart-badge';
            cartLink.appendChild(badge);
        }
    }
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
        badge.classList.add('badge-pop');
        setTimeout(() => badge.classList.remove('badge-pop'), 300);
    }
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 2500);
}
</script>
@endpush
@endsection
