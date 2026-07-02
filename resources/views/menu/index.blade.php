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
    {{-- Modern Dribbble-style Hero Banner --}}
    <div class="menu-hero">
        <div style="flex: 1; max-width: 520px; text-align: left; z-index: 2;">
            <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.18); padding: 0.35rem 0.9rem; border-radius: 50px; font-size: 0.82rem; font-weight: 700; margin-bottom: 1rem; border: 1px solid rgba(255,255,255,0.25); backdrop-filter: blur(8px);">
                <span>🔥</span> Sensasi Kuliner Pedas #1
            </div>
            <h1>Nikmati Sego Sambelan<br>Autentik & Gurih!</h1>
            <p style="margin-bottom: 1.6rem;">Diramu dengan resep rahasia tradisional dan sambal dadakan super pedas yang bikin nagih di setiap suapan.</p>
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <a href="#menu-catalog" class="btn" style="background: #FFFFFF; color: #9A3412; font-weight: 800; border-radius: 50px; padding: 0.75rem 1.6rem; box-shadow: 0 4px 15px rgba(0,0,0,0.15); display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
                    <span>🍽️</span> Jelajahi Menu
                </a>
                <div style="display: flex; align-items: center; gap: 6px; color: rgba(255,255,255,0.95); font-size: 0.85rem; font-weight: 600; padding: 0.5rem 0.8rem; background: rgba(0,0,0,0.15); border-radius: 50px;">
                    <span>⚡ Pengiriman Cepat</span>
                </div>
            </div>
        </div>
        <div style="flex-shrink: 0; display: flex; justify-content: center; align-items: center; width: 145px; height: 145px; background: rgba(255,255,255,0.12); border-radius: 50%; border: 2px solid rgba(255,255,255,0.25); box-shadow: 0 12px 30px rgba(0,0,0,0.25); z-index: 2;" class="hero-icon-wrap">
            <img src="{{ asset('images/icons/icon_semua.png') }}" alt="Menu Spesial" style="width: 110px; height: 110px; object-fit: contain; filter: drop-shadow(0 8px 16px rgba(0,0,0,0.3)); transform: scale(1.1);">
        </div>
    </div>

    {{-- Live Search Pill Bar --}}
    <div id="menu-catalog" style="scroll-margin-top: 100px;"></div>
    <div style="max-width: 540px; margin: 0 auto 1.8rem auto; position: relative;">
        <span style="position: absolute; left: 1.3rem; top: 50%; transform: translateY(-50%); font-size: 1.15rem; opacity: 0.65;">🔍</span>
        <input type="text" id="menuSearchInput" placeholder="Cari menu favoritmu (cth: ayam, jeruk, sambal)..."
               style="width: 100%; padding: 0.95rem 1.5rem 0.95rem 3.4rem; border-radius: 50px; border: 2px solid #E5E7EB; background: #FFFFFF; font-size: 0.95rem; font-weight: 600; color: #1F2937; box-shadow: 0 6px 20px rgba(0,0,0,0.03); outline: none; transition: all 0.3s;"
               onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 8px 25px rgba(234, 88, 12, 0.15)'"
               onblur="this.style.borderColor='#E5E7EB'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.03)'">
    </div>

    {{-- Category Tabs --}}
    <div class="category-tabs">
        <a href="/menu" class="tab {{ !request('category') ? 'active' : '' }}" style="display: inline-flex; align-items: center; gap: 8px;">
            <img src="{{ asset('images/icons/icon_semua.png') }}" alt="Semua" style="height: 20px; width: 20px; object-fit: contain;"> Semua
        </a>
        <a href="/menu?category=food" class="tab {{ request('category') === 'food' ? 'active' : '' }}" style="display: inline-flex; align-items: center; gap: 8px;">
            <img src="{{ asset('images/icons/icon_makanan.png') }}" alt="Makanan" style="height: 20px; width: 20px; object-fit: contain;"> Makanan
        </a>
        <a href="/menu?category=drink" class="tab {{ request('category') === 'drink' ? 'active' : '' }}" style="display: inline-flex; align-items: center; gap: 8px;">
            <img src="{{ asset('images/icons/icon_minuman.png') }}" alt="Minuman" style="height: 20px; width: 20px; object-fit: contain;"> Minuman
        </a>
    </div>

    {{-- Products Grid --}}
    <div class="products-grid" id="productsGrid">
        @forelse($products as $product)
        <div class="product-card glass-card" id="product-{{ $product->id }}">
            <div class="product-image-wrap">
                @if($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-image" loading="lazy">
                @else
                <div class="product-image-placeholder" style="display: flex; align-items: center; justify-content: center; height: 100%; background: #FFF7ED;">
                    <img src="{{ asset('images/icons/' . ($product->category === 'food' ? 'icon_makanan.png' : 'icon_minuman.png')) }}" alt="{{ $product->category }}" style="height: 85px; width: 85px; object-fit: contain; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">
                </div>
                @endif
                <span class="product-category-badge badge-{{ $product->category }}">
                    {{ $product->category === 'food' ? 'Makanan' : 'Minuman' }}
                </span>
                <span class="product-rating-badge">
                    <span>⭐</span> {{ number_format(4.7 + (($product->id % 3) * 0.1), 1) }}
                </span>
            </div>
            <div class="product-info">
                <h3 class="product-name">{{ $product->name }}</h3>
                <p class="product-desc">{{ Str::limit($product->description, 50) }}</p>
                <div class="product-footer">
                    <span class="product-price">{{ $product->formatted_price }}</span>
                    @if($product->isInStock())
                    <button class="btn-add-to-cart"
                            onclick="addToCart({{ $product->id }}, this)"
                            data-product-id="{{ $product->id }}"
                            data-requires-auth="{{ auth()->check() ? 'false' : 'true' }}"
                            title="Tambah ke Keranjang">
                        <span style="font-size: 1.1rem; line-height: 1;">+</span>
                        <span>Pesan</span>
                    </button>
                    @else
                    <span class="badge badge-danger" style="font-size: 0.7rem; padding: 0.35rem 0.7rem; border-radius: 50px;">Habis</span>
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
    btn.innerHTML = '<span style="font-size: 1rem;">✓</span><span>Masuk Keranjang</span>';
    btn.disabled = true;
    btn.classList.add('btn-success');

    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
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
    // 1. Update Navbar Badge
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

    // 2. Update Floating Cart Badge
    let floatCart = document.getElementById('floating-cart');
    let floatBadge = document.getElementById('floating-cart-badge');
    if (floatCart && floatBadge) {
        floatBadge.textContent = count;
        if (count > 0) {
            floatCart.classList.add('show');
            floatBadge.classList.add('badge-pop');
            setTimeout(() => floatBadge.classList.remove('badge-pop'), 300);
        } else {
            floatCart.classList.remove('show');
        }
    }
}

// Live Search Filter (Dribbble App Style)
document.getElementById('menuSearchInput')?.addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase().trim();
    const cards = document.querySelectorAll('.product-card');
    let visibleCount = 0;
    cards.forEach(card => {
        const name = card.querySelector('.product-name')?.textContent.toLowerCase() || '';
        const desc = card.querySelector('.product-desc')?.textContent.toLowerCase() || '';
        if (name.includes(term) || desc.includes(term)) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    let emptySearch = document.getElementById('emptySearchNotice');
    if (visibleCount === 0 && cards.length > 0) {
        if (!emptySearch) {
            emptySearch = document.createElement('div');
            emptySearch.id = 'emptySearchNotice';
            emptySearch.className = 'empty-state';
            emptySearch.style.gridColumn = '1 / -1';
            emptySearch.style.padding = '3rem 1rem';
            emptySearch.innerHTML = '<span class="empty-icon" style="font-size:3rem;display:block;margin-bottom:1rem;">🔍</span><p style="font-weight:700;font-size:1.1rem;color:#4B5563;">Menu "' + e.target.value + '" tidak ditemukan.</p>';
            document.getElementById('productsGrid')?.appendChild(emptySearch);
        } else {
            emptySearch.style.display = 'block';
            emptySearch.querySelector('p').textContent = 'Menu "' + e.target.value + '" tidak ditemukan.';
        }
    } else if (emptySearch) {
        emptySearch.style.display = 'none';
    }
});
</script>
@endpush
@endsection
