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
                <label>Pin Lokasi (Opsional)</label>
                <div id="map" class="map-container"></div>
                <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $profile->latitude ?? '') }}">
                <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $profile->longitude ?? '') }}">
                <small class="form-hint">Klik pada peta untuk menandai lokasi Anda</small>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">
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
    const lat = parseFloat(document.getElementById('latitude').value) || -7.2575;
    const lng = parseFloat(document.getElementById('longitude').value) || 112.7521;
    const map = L.map('map').setView([lat, lng], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);
    
    let marker = L.marker([lat, lng], {draggable: true}).addTo(map);

    function updateLocation(lat, lng) {
        document.getElementById('latitude').value = lat.toFixed(8);
        document.getElementById('longitude').value = lng.toFixed(8);
        
        // Reverse Geocoding menggunakan Nominatim OpenStreetMap
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                if (data.address) {
                    const addr = data.address;
                    
                    // Tentukan Alamat Jalan
                    const street = addr.road || addr.pedestrian || addr.suburb || addr.neighbourhood || '';
                    if (street && !document.getElementById('street_address').value) {
                        document.getElementById('street_address').value = street;
                    } else if (street) {
                        document.getElementById('street_address').value = street; // Memaksa update
                    }
                    
                    // Tentukan Kota/Kabupaten
                    const city = addr.city || addr.town || addr.village || addr.county || '';
                    if (city) document.getElementById('city').value = city;
                    
                    // Tentukan Provinsi
                    const state = addr.state || addr.region || '';
                    if (state) document.getElementById('province').value = state;
                    
                    // Tentukan Kode Pos
                    const postcode = addr.postcode || '';
                    if (postcode) document.getElementById('postal_code').value = postcode;
                }
            })
            .catch(error => console.error('Error fetching address:', error));
    }

    marker.on('dragend', function(e) {
        updateLocation(e.target.getLatLng().lat, e.target.getLatLng().lng);
    });
    
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateLocation(e.latlng.lat, e.latlng.lng);
    });
});
</script>
@endpush
@endsection
