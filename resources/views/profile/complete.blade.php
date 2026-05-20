@extends('layouts.app')
@section('title', 'Lengkapi Profil - Sego Sambelan')

@section('content')
<div class="container py-2">
    <div class="profile-banner">
        <span class="profile-banner-icon">📍</span>
        <h1>Lengkapi Data Alamat Anda</h1>
        <p>Kami membutuhkan alamat lengkap Anda untuk pengiriman pesanan. Data ini wajib diisi sebelum Anda dapat memesan.</p>
    </div>

    <div class="card glass-card profile-form-card">
        <form method="POST" action="/profile/complete" class="profile-form">
            @csrf
            <div class="form-group">
                <label for="phone">Nomor Telepon / WhatsApp <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone', $profile->phone ?? '') }}" placeholder="08123456789" required class="form-input @error('phone') input-error @enderror">
                @error('phone')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="street_address">Alamat Jalan <span class="required">*</span></label>
                <input type="text" id="street_address" name="street_address" value="{{ old('street_address', $profile->street_address ?? '') }}" placeholder="Jl. Contoh No. 123, RT 01/RW 02" required class="form-input @error('street_address') input-error @enderror">
                @error('street_address')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city">Kota / Kabupaten <span class="required">*</span></label>
                    <input type="text" id="city" name="city" value="{{ old('city', $profile->city ?? '') }}" placeholder="Surabaya" required class="form-input @error('city') input-error @enderror">
                    @error('city')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="province">Provinsi <span class="required">*</span></label>
                    <input type="text" id="province" name="province" value="{{ old('province', $profile->province ?? '') }}" placeholder="Jawa Timur" required class="form-input @error('province') input-error @enderror">
                    @error('province')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-group">
                <label for="postal_code">Kode Pos <span class="required">*</span></label>
                <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $profile->postal_code ?? '') }}" placeholder="60115" required class="form-input @error('postal_code') input-error @enderror" maxlength="10">
                @error('postal_code')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            {{-- Map Pin (Optional) --}}
            <div class="form-group">
                <label>Pin Lokasi di Peta <span class="required">*</span></label>
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <input type="text" id="map-search-input" placeholder="Cari nama jalan / komplek / perumahan..." class="form-input" style="flex-grow: 1; margin: 0;">
                    <button type="button" id="btn-search-map" class="btn btn-outline" style="white-space: nowrap; padding: 0.5rem 1rem; border-color: rgba(232,184,75,0.4); color: var(--primary);">🔍 Cari</button>
                </div>
                <div id="map" class="map-container" style="height: 320px; border-radius: var(--radius-sm); border: 1px solid var(--border); box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); margin-bottom: 0.5rem; z-index: 1;"></div>
                <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $profile->latitude ?? '') }}">
                <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $profile->longitude ?? '') }}">
                <small class="form-hint" style="color: var(--text-secondary);">Geser pin kuning atau klik pada peta untuk menandai lokasi persis pengiriman Anda.</small>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top: 1rem;">
                Simpan & Mulai Pesan 🍛
            </button>
        </form>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    
    // Default coordinates: Surabaya
    let lat = parseFloat(latInput.value) || -7.2575; 
    let lng = parseFloat(lngInput.value) || 112.7521;
    
    const map = L.map('map').setView([lat, lng], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);
    
    let marker = L.marker([lat, lng], {draggable: true}).addTo(map);

    function updateLocationFields(latVal, lngVal, reverseGeocode = true) {
        latInput.value = latVal.toFixed(8);
        lngInput.value = lngVal.toFixed(8);
        
        if (reverseGeocode) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latVal}&lon=${lngVal}&accept-language=id`)
                .then(response => response.json())
                .then(data => {
                    if (data.address) {
                        const addr = data.address;
                        
                        // Extract road and village/neighborhood for a clean street address
                        const road = addr.road || addr.pedestrian || '';
                        const suburb = addr.suburb || addr.village || addr.neighbourhood || '';
                        let street = road;
                        if (suburb) {
                            street = street ? street + ', ' + suburb : suburb;
                        }
                        
                        if (street) {
                            document.getElementById('street_address').value = street;
                        }
                        
                        // City / Kabupaten
                        const city = addr.city || addr.town || addr.village || addr.county || '';
                        if (city) {
                            document.getElementById('city').value = city.replace('Kota ', '').replace('Kabupaten ', '');
                        }
                        
                        // Province
                        const state = addr.state || '';
                        if (state) {
                            document.getElementById('province').value = state;
                        }
                        
                        // Postal Code
                        const postcode = addr.postcode || '';
                        if (postcode) {
                            document.getElementById('postal_code').value = postcode;
                        }
                    }
                })
                .catch(error => console.error('Error reverse geocoding:', error));
        }
    }

    // Drag marker event
    marker.on('dragend', function(e) {
        const position = e.target.getLatLng();
        updateLocationFields(position.lat, position.lng, true);
    });
    
    // Map click event
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateLocationFields(e.latlng.lat, e.latlng.lng, true);
    });

    // Search location functionality
    const searchInput = document.getElementById('map-search-input');
    const searchBtn = document.getElementById('btn-search-map');

    function performSearch() {
        const query = searchInput.value.trim();
        if (!query) return;

        searchBtn.innerHTML = '⚡ Mencari...';
        searchBtn.disabled = true;

        // Fetch location with priority for Indonesia
        const searchUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1&accept-language=id&countrycodes=id`;

        fetch(searchUrl)
            .then(response => response.json())
            .then(results => {
                if (results && results.length > 0) {
                    const result = results[0];
                    const newLat = parseFloat(result.lat);
                    const newLng = parseFloat(result.lon);

                    // Pan to results and set pin
                    map.setView([newLat, newLng], 16);
                    marker.setLatLng([newLat, newLng]);

                    // Update fields
                    updateLocationFields(newLat, newLng, true);
                } else {
                    alert('Lokasi tidak ditemukan. Silakan masukkan nama jalan, komplek, atau wilayah yang lebih spesifik.');
                }
            })
            .catch(error => {
                console.error('Geocoding error:', error);
                alert('Gagal mencari lokasi. Silakan coba lagi.');
            })
            .finally(() => {
                searchBtn.innerHTML = '🔍 Cari';
                searchBtn.disabled = false;
            });
    }

    // Search click
    searchBtn.addEventListener('click', performSearch);

    // Search Enter key
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });

    // Auto HTML5 Geolocation on empty coordinates
    if (navigator.geolocation && !latInput.value) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            map.setView([userLat, userLng], 16);
            marker.setLatLng([userLat, userLng]);
            updateLocationFields(userLat, userLng, true);
        }, function(err) {
            console.log('HTML5 Geolocation declined or failed:', err);
        });
    }
});
</script>
@endpush
@endsection
