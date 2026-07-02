@extends('layouts.app')
@section('title', 'Checkout - Sego Sambelan')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #addressMap { height: 200px; width: 100%; border-radius: 0; z-index: 1; }
    .address-card { transition: all 0.2s ease; }
    .address-card input:checked ~ div .radio-indicator { background: var(--primary) !important; box-shadow: inset 0 0 0 3px white; }
    .address-card:has(input:checked) { border-color: var(--primary) !important; background: var(--primary-light) !important; }
    .leaflet-container { font-family: inherit; }
    .map-search-wrapper { position: relative; }
    .map-search-wrapper input { padding-left: 2.5rem; }
    .map-search-icon { position: absolute; left: 0.8rem; top: 50%; transform: translateY(-50%); color: #9CA3AF; pointer-events: none; font-size: 1.1rem; }
    .map-hint { font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.5rem; text-align: center; }
    #modalMap { height: 220px; width: 100%; border-radius: var(--radius-sm); z-index: 1; }
    .address-modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
    .address-modal-box { background: white; width: 100%; max-width: 500px; border-radius: var(--radius); padding: 0; margin: 1rem; max-height: 92vh; overflow-y: auto; }
    .address-modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #E5E7EB; }
    .address-modal-body { padding: 1.25rem 1.5rem; }
    .address-detail-input { margin-top: 1rem; }
</style>
@endpush

@section('content')
<div class="container py-2">
    <h1 class="page-title">Checkout</h1>

    <form method="POST" action="/checkout/place-order" class="checkout-layout">
        @csrf

        {{-- Order Type Selection --}}
        <div class="checkout-section card">
            <h2>Pilih Tipe Pesanan</h2>
            <div class="order-type-cards">
                <label class="order-type-card" id="delivery-card">
                    <input type="radio" name="order_type" value="delivery" {{ old('order_type', 'delivery') === 'delivery' ? 'checked' : '' }} onchange="toggleOrderType()">
                    <div class="order-type-content">
                        <img src="{{ asset('images/icons/icon_delivery.svg') }}" alt="Delivery" style="height: 38px; width: 38px; object-fit: contain; margin-bottom: 0.5rem; filter: drop-shadow(0 2px 4px rgba(234, 88, 12, 0.15));">
                        <h3>Delivery</h3>
                        <p>Kirim ke alamat Anda</p>
                    </div>
                </label>
                <label class="order-type-card" id="dinein-card">
                    <input type="radio" name="order_type" value="dine_in" {{ old('order_type') === 'dine_in' ? 'checked' : '' }} onchange="toggleOrderType()">
                    <div class="order-type-content">
                        <img src="{{ asset('images/icons/icon_dinein.svg') }}" alt="Dine-In" style="height: 38px; width: 38px; object-fit: contain; margin-bottom: 0.5rem; filter: drop-shadow(0 2px 4px rgba(217, 119, 6, 0.15));">
                        <h3>Dine-In</h3>
                        <p>Makan di tempat</p>
                    </div>
                </label>
            </div>
            @error('order_type')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        {{-- Delivery Address Section --}}
        <div id="delivery-info" class="checkout-section card" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="margin: 0;">Alamat Pengiriman</h2>
                <button type="button" onclick="openAddressModal()" class="btn" style="background: var(--primary-light); color: var(--primary-dark); font-size: 0.85rem; padding: 0.4rem 0.8rem; border-radius: 20px;">
                    + Tambah Alamat
                </button>
            </div>

            @if($addresses->isEmpty())
            <div style="padding: 2rem; text-align: center; background: #F9FAFB; border: 1px dashed #D1D5DB; border-radius: var(--radius-sm);">
                <div style="font-size: 2.5rem; margin-bottom: 0.8rem;">📍</div>
                <p style="color: var(--text-secondary); margin-bottom: 1rem; font-size: 0.95rem;">Anda belum memiliki alamat tersimpan.<br>Tambahkan alamat untuk melanjutkan pesanan delivery.</p>
                <button type="button" onclick="openAddressModal()" class="btn btn-primary">Tambah Alamat Sekarang</button>
            </div>
            @else
            <div class="address-list" style="display: flex; flex-direction: column; gap: 0.8rem;">
                @foreach($addresses as $addr)
                <label class="address-card" style="display: block; border: 1px solid {{ $addr->is_primary ? 'var(--primary)' : '#E5E7EB' }}; background: {{ $addr->is_primary ? 'var(--primary-light)' : '#FFFFFF' }}; padding: 1rem; border-radius: var(--radius-sm); cursor: pointer; position: relative;">
                    <input type="radio" name="address_id" value="{{ $addr->id }}" {{ (old('address_id') == $addr->id || ($loop->first && !old('address_id'))) ? 'checked' : '' }} style="position: absolute; opacity: 0;">
                    <div style="display: flex; gap: 0.8rem;">
                        <div style="margin-top: 0.15rem; flex-shrink: 0;">
                            <span style="display: inline-block; width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--primary); background: white;" class="radio-indicator"></span>
                        </div>
                        <div style="flex-grow: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                <span style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;">{{ $addr->label }}</span>
                                @if($addr->is_primary)
                                <span style="font-size: 0.7rem; background: var(--primary); color: white; padding: 0.15rem 0.5rem; border-radius: 10px; font-weight: 600;">Utama</span>
                                @endif
                            </div>
                            <p style="margin: 0; color: var(--text-secondary); font-size: 0.85rem; line-height: 1.4;">{{ $addr->full_address }}</p>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
            @endif
            @error('address_id')<span class="form-error" style="display:block; margin-top:0.5rem;">{{ $message }}</span>@enderror
        </div>

        {{-- Dine-In --}}
        <div id="dinein-info" class="checkout-section card" style="display:none;">
            <h2>Makan di Tempat</h2>
            <div class="form-group">
                <label for="table_number">Nomor Meja</label>
                <input type="text" id="table_number" name="table_number" value="{{ old('table_number') }}" placeholder="Contoh: 5" class="form-input @error('table_number') input-error @enderror">
                @error('table_number')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        {{-- Order Items --}}
        <div class="checkout-section card">
            <h2>Pesanan Anda</h2>
            <div class="checkout-items">
                @foreach($cart as $productId => $item)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.6rem 0; border-bottom: 1px solid #F3F4F6;">
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <span style="color: var(--primary); font-weight: 700; min-width: 24px;">{{ $item['quantity'] }}x</span>
                        <span style="color: var(--text-primary); font-weight: 600;">{{ $item['name'] }}</span>
                    </div>
                    <span style="color: var(--text-secondary); font-weight: 500;">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Payment Method --}}
        <div class="checkout-section card">
            <h2>Metode Pembayaran</h2>
            <div class="order-type-cards">
                <label class="order-type-card" style="padding: 1rem; border-radius: var(--radius-sm);">
                    <input type="radio" name="payment_channel" value="midtrans" {{ old('payment_channel', 'midtrans') === 'midtrans' ? 'checked' : '' }} onchange="updatePayButton()">
                    <div class="order-type-content" style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 0.8rem;">
                            <img src="{{ asset('images/icons/icon_card.svg') }}" alt="Otomatis" style="height: 26px; width: 26px; object-fit: contain;">
                            <h3 style="margin: 0; font-size: 1rem;">Otomatis (QRIS, VA, E-Wallet)</h3>
                        </div>
                    </div>
                </label>
                @if($paymentSettings->manual_enabled && $paymentSettings->isConfigured())
                <label class="order-type-card" style="padding: 1rem; border-radius: var(--radius-sm);">
                    <input type="radio" name="payment_channel" value="manual" {{ old('payment_channel') === 'manual' ? 'checked' : '' }} onchange="updatePayButton()">
                    <div class="order-type-content" style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 0.8rem;">
                            <img src="{{ asset('images/icons/icon_bank.svg') }}" alt="Transfer Manual" style="height: 26px; width: 26px; object-fit: contain;">
                            <h3 style="margin: 0; font-size: 1rem;">Transfer Manual</h3>
                        </div>
                    </div>
                </label>
                @endif
            </div>
            @error('payment_channel')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        {{-- Notes --}}
        <div class="checkout-section card">
            <div class="form-group">
                <label for="notes">Catatan Khusus (Opsional)</label>
                <textarea id="notes" name="notes" placeholder="Contoh: Sambalnya extra pedas, tanpa timun..." class="form-input form-textarea" style="background: #F9FAFB; border: none;">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Summary --}}
        <div class="checkout-section">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: var(--text-secondary);">Total Makanan</span>
                <span style="font-weight: 600;">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                <span style="color: var(--text-secondary);">Ongkos Kirim</span>
                <span style="font-weight: 600;">Rp 0</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-top: 1px solid #E5E7EB; padding-top: 1rem;">
                <span style="font-weight: 700; color: var(--text-primary);">Total Akhir</span>
                <span style="font-weight: 700; color: var(--primary); font-size: 1.2rem;">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <button type="submit" id="pay-submit-btn" class="btn btn-primary btn-full" style="padding: 1rem; border-radius: var(--radius); font-size: 1.1rem; margin-top: 1rem;">
                BAYAR SEKARANG
            </button>
        </div>
    </form>
</div>

{{-- ====== MODAL: Tambah Alamat Baru ====== --}}
<div id="addressModal" class="address-modal-overlay">
    <div class="address-modal-box">
        <div class="address-modal-header">
            <h3 style="margin: 0; font-size: 1.15rem;">Tambah Alamat Baru</h3>
            <button type="button" onclick="closeAddressModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6B7280; line-height: 1;">&times;</button>
        </div>

        <div class="address-modal-body">
            <form id="addAddressForm" onsubmit="submitNewAddress(event)">
                @csrf

                {{-- Label --}}
                <div class="form-group">
                    <label style="font-weight: 600; font-size: 0.9rem;">Label Alamat</label>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.4rem;">
                        <button type="button" class="label-chip active" onclick="selectLabel(this, 'Rumah')">🏠 Rumah</button>
                        <button type="button" class="label-chip" onclick="selectLabel(this, 'Kos')">🏘️ Kos</button>
                        <button type="button" class="label-chip" onclick="selectLabel(this, 'Kantor')">🏢 Kantor</button>
                        <button type="button" class="label-chip" onclick="selectLabel(this, 'Lainnya')">📍 Lainnya</button>
                    </div>
                    <input type="hidden" name="label" id="addressLabel" value="Rumah" required>
                </div>

                {{-- Search Address --}}
                <div class="form-group">
                    <label style="font-weight: 600; font-size: 0.9rem;">Cari Lokasi di Peta</label>
                    <div class="map-search-wrapper">
                        <span class="map-search-icon">🔍</span>
                        <input type="text" id="mapSearchInput" placeholder="Ketik alamat lengkap pengiriman..." class="form-input" style="padding-left: 2.5rem;" autocomplete="off">
                    </div>
                    <div id="searchResults" style="display:none; position: absolute; z-index: 1001; background: white; border: 1px solid #E5E7EB; border-radius: var(--radius-sm); max-height: 200px; overflow-y: auto; width: calc(100% - 3rem); box-shadow: 0 4px 12px rgba(0,0,0,0.1);"></div>
                </div>

                {{-- Interactive Map --}}
                <div style="border-radius: var(--radius-sm); overflow: hidden; border: 1px solid #E5E7EB; margin-bottom: 0.5rem;">
                    <div id="modalMap"></div>
                </div>
                <p class="map-hint">📍 Geser pin merah untuk menentukan lokasi yang tepat</p>

                {{-- Detail Address --}}
                <div class="form-group address-detail-input">
                    <label style="font-weight: 600; font-size: 0.9rem;">Detail Alamat / Patokan</label>
                    <textarea name="full_address" id="fullAddressInput" placeholder="Nomor Rumah / Patokan (Contoh: Pagar Hitam di sebelah konter)" required class="form-input form-textarea" style="height: 80px;"></textarea>
                </div>

                <input type="hidden" name="latitude" id="latInput">
                <input type="hidden" name="longitude" id="lngInput">

                <button type="submit" id="saveAddressBtn" class="btn btn-primary btn-full" style="padding: 0.9rem; font-size: 1rem; margin-top: 0.5rem;">
                    Simpan Alamat
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.label-chip {
    padding: 0.4rem 0.8rem;
    border: 1px solid #E5E7EB;
    border-radius: 20px;
    background: white;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--text-secondary);
    transition: all 0.2s;
}
.label-chip:hover { border-color: var(--primary); color: var(--primary); }
.label-chip.active { background: var(--primary); color: white; border-color: var(--primary); }
.search-result-item {
    padding: 0.6rem 0.8rem;
    cursor: pointer;
    font-size: 0.85rem;
    color: #374151;
    border-bottom: 1px solid #F3F4F6;
    transition: background 0.15s;
}
.search-result-item:hover { background: #F9FAFB; }
.search-result-item:last-child { border-bottom: none; }
</style>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── Map & Address Variables ──
let modalMap = null;
let modalMarker = null;
let searchTimeout = null;
const DEFAULT_LAT = -7.2756;  // Sidoarjo area
const DEFAULT_LNG = 112.7181;

// ── Order Type Toggle ──
function toggleOrderType() {
    const delivery = document.querySelector('input[name="order_type"][value="delivery"]').checked;
    document.getElementById('delivery-info').style.display = delivery ? 'block' : 'none';
    document.getElementById('dinein-info').style.display = delivery ? 'none' : 'block';
}

// ── Pay Button ──
function updatePayButton() {
    const manual = document.querySelector('input[name="payment_channel"][value="manual"]');
    const btn = document.getElementById('pay-submit-btn');
    if (manual && manual.checked) {
        btn.textContent = 'Lanjut ke Instruksi Transfer 🏦';
    } else {
        btn.textContent = 'Bayar Sekarang 💳';
    }
}

// ── Label Chip Selector ──
function selectLabel(el, label) {
    document.querySelectorAll('.label-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('addressLabel').value = label;
}

// ── Address Modal ──
function openAddressModal() {
    document.getElementById('addressModal').style.display = 'flex';
    setTimeout(() => {
        initModalMap();
    }, 300);
}

function closeAddressModal() {
    document.getElementById('addressModal').style.display = 'none';
    document.getElementById('addAddressForm').reset();
    document.querySelectorAll('.label-chip').forEach((c, i) => {
        c.classList.toggle('active', i === 0);
    });
    document.getElementById('addressLabel').value = 'Rumah';
    document.getElementById('searchResults').style.display = 'none';
}

// ── Initialize Leaflet Map ──
function initModalMap() {
    if (modalMap) {
        modalMap.invalidateSize();
        return;
    }

    modalMap = L.map('modalMap', {
        center: [DEFAULT_LAT, DEFAULT_LNG],
        zoom: 15,
        zoomControl: false
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 19
    }).addTo(modalMap);

    L.control.zoom({ position: 'bottomright' }).addTo(modalMap);

    // Red marker icon
    const redIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    modalMarker = L.marker([DEFAULT_LAT, DEFAULT_LNG], {
        draggable: true,
        icon: redIcon
    }).addTo(modalMap);

    // When marker is dragged, reverse-geocode the new position
    modalMarker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        document.getElementById('latInput').value = pos.lat.toFixed(8);
        document.getElementById('lngInput').value = pos.lng.toFixed(8);
        reverseGeocode(pos.lat, pos.lng);
    });

    // Set initial hidden values
    document.getElementById('latInput').value = DEFAULT_LAT;
    document.getElementById('lngInput').value = DEFAULT_LNG;

    // Try to get user's current location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            modalMap.setView([lat, lng], 16);
            modalMarker.setLatLng([lat, lng]);
            document.getElementById('latInput').value = lat.toFixed(8);
            document.getElementById('lngInput').value = lng.toFixed(8);
            reverseGeocode(lat, lng);
        }, function() {
            // Geolocation denied, use default
        }, { timeout: 5000 });
    }
}

// ── Reverse Geocode (Nominatim) ──
function reverseGeocode(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
        headers: { 'Accept-Language': 'id' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.display_name) {
            document.getElementById('mapSearchInput').value = data.display_name;
        }
    })
    .catch(() => {});
}

// ── Search Address (Nominatim) ──
document.addEventListener('DOMContentLoaded', function() {
    toggleOrderType();
    updatePayButton();

    const searchInput = document.getElementById('mapSearchInput');
    const searchResults = document.getElementById('searchResults');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            if (query.length < 3) {
                searchResults.style.display = 'none';
                return;
            }
            searchTimeout = setTimeout(() => searchAddress(query), 600);
        });

        // Close results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }

    // Auto-select first address
    const addressRadios = document.querySelectorAll('input[name="address_id"]');
    if (addressRadios.length > 0 && !document.querySelector('input[name="address_id"]:checked')) {
        addressRadios[0].checked = true;
    }
});

function searchAddress(query) {
    const searchResults = document.getElementById('searchResults');
    // Bias search toward Indonesia
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&countrycodes=id&addressdetails=1`, {
        headers: { 'Accept-Language': 'id' }
    })
    .then(r => r.json())
    .then(results => {
        searchResults.innerHTML = '';
        if (results.length === 0) {
            searchResults.innerHTML = '<div class="search-result-item" style="color:#9CA3AF;">Alamat tidak ditemukan</div>';
            searchResults.style.display = 'block';
            return;
        }
        results.forEach(result => {
            const div = document.createElement('div');
            div.className = 'search-result-item';
            div.textContent = result.display_name;
            div.onclick = () => selectSearchResult(result);
            searchResults.appendChild(div);
        });
        searchResults.style.display = 'block';
    })
    .catch(() => {
        searchResults.style.display = 'none';
    });
}

function selectSearchResult(result) {
    const lat = parseFloat(result.lat);
    const lng = parseFloat(result.lon);

    document.getElementById('mapSearchInput').value = result.display_name;
    document.getElementById('searchResults').style.display = 'none';
    document.getElementById('latInput').value = lat.toFixed(8);
    document.getElementById('lngInput').value = lng.toFixed(8);

    if (modalMap && modalMarker) {
        modalMap.setView([lat, lng], 17);
        modalMarker.setLatLng([lat, lng]);
    }
}

// ── Submit New Address ──
async function submitNewAddress(e) {
    e.preventDefault();
    const btn = document.getElementById('saveAddressBtn');
    const fullAddress = document.getElementById('fullAddressInput').value.trim();
    const mapAddress = document.getElementById('mapSearchInput').value.trim();

    if (!fullAddress) {
        alert('Mohon isi detail alamat / patokan.');
        return;
    }

    // Combine map address + detail into full_address
    const combinedAddress = mapAddress ? `${fullAddress} — ${mapAddress}` : fullAddress;
    document.getElementById('fullAddressInput').value = combinedAddress;

    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    const formData = new FormData(e.target);
    try {
        const response = await fetch('/checkout/address', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();

        if (data.success) {
            window.location.reload();
        } else {
            alert('Gagal menyimpan alamat. Pastikan semua field terisi.');
            btn.disabled = false;
            btn.textContent = 'Simpan Alamat';
        }
    } catch (error) {
        console.error(error);
        alert('Terjadi kesalahan sistem.');
        btn.disabled = false;
        btn.textContent = 'Simpan Alamat';
    }
}
</script>
@endpush
@endsection
