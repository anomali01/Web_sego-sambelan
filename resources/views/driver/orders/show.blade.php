@extends('layouts.app')
@section('title', 'Detail Tugas - Sego Sambelan')

@section('content')
<div class="container py-2">
    <div class="flex-between align-center" style="margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin-bottom: 0;">Detail Pengantaran</h1>
        <a href="{{ route('driver.orders.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card" style="padding: 1.5rem; border-radius: var(--radius);">
        <div style="margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Pesanan #{{ $order->order_number }}</h2>
            <span class="badge {{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span>
        </div>

        <div style="margin-bottom: 1.5rem; background: var(--bg-color); padding: 1rem; border-radius: var(--radius-sm);">
            <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--text-color);">👤 Informasi Pembeli</h3>
            <p><strong>Nama:</strong> {{ $order->user->name }}</p>
            <p><strong>No. HP:</strong> {{ $order->user->profile->phone ?? '-' }}</p>
            <hr style="margin: 0.75rem 0; border: none; border-top: 1px dashed #E5E7EB;">
            <p><strong>Lokasi Pengantaran:</strong></p>
            <p style="font-size: 0.95rem; line-height: 1.5;">{{ $order->delivery_address }}</p>
            @if($order->notes)
            <div style="margin-top: 0.5rem; padding: 0.5rem; background: #FEF3C7; color: #92400E; border-radius: 4px; font-size: 0.9rem;">
                <strong>Catatan:</strong> {{ $order->notes }}
            </div>
            @endif
        </div>

        <div style="margin-bottom: 2rem;">
            <!-- Maps Link using Google Maps App/Web -->
            @if($order->delivery_latitude && $order->delivery_longitude)
                {{-- Gunakan koordinat yang tepat untuk navigasi --}}
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $order->delivery_latitude }},{{ $order->delivery_longitude }}" target="_blank" class="btn btn-primary btn-full btn-lg" style="background-color: #3B82F6; border-color: #3B82F6;">
                    🗺️ Buka Navigasi Google Maps
                </a>
            @else
                {{-- Fallback: gunakan alamat teks --}}
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($order->delivery_address) }}" target="_blank" class="btn btn-primary btn-full btn-lg" style="background-color: #3B82F6; border-color: #3B82F6;">
                    🗺️ Buka Navigasi Google Maps
                </a>
            @endif
            <p style="text-align: center; font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">Akan membuka aplikasi Google Maps dengan mode navigasi</p>
        </div>

        <h3 style="font-size: 1rem; margin-bottom: 0.75rem;">Ubah Status Pesanan</h3>
        @if($order->status === 'processed')
        <form action="{{ route('driver.orders.status', $order->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="delivering">
            <button type="submit" class="btn btn-primary btn-full" style="background-color: #F59E0B; border-color: #F59E0B;" onclick="return confirm('Mulai mengantar pesanan ini?')">
                🛵 Mulai Diantar
            </button>
        </form>
        @elseif($order->status === 'delivering')
        <form action="{{ route('driver.orders.status', $order->id) }}" method="POST" enctype="multipart/form-data" style="background: var(--bg-color); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border);">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="delivered">
            
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="delivery_proof" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">📸 Unggah Foto Bukti Pengantaran</label>
                <input type="file" name="delivery_proof" id="delivery_proof" class="form-control" accept="image/*" required style="width: 100%;">
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Wajib mengunggah foto paket yang telah diterima pembeli atau di lokasi pengantaran.</p>
                @error('delivery_proof')
                    <span style="color: red; font-size: 0.8rem;">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-full" style="background-color: #10B981; border-color: #10B981;" onclick="return confirm('Konfirmasi pesanan telah sampai di tangan pembeli dan bukti foto sudah benar?')">
                ✅ Selesaikan Pengantaran
            </button>
        </form>
        @elseif($order->status === 'delivered')
        <div style="text-align: center; padding: 1rem; background: #D1FAE5; color: #065F46; border-radius: var(--radius-sm);">
            🎉 Menunggu Verifikasi Admin
            @if($order->delivery_proof_url)
            <div style="margin-top: 1rem;">
                <p style="font-size: 0.85rem; margin-bottom: 0.5rem;">Bukti Pengantaran:</p>
                <img src="{{ $order->delivery_proof_url }}" alt="Bukti Pengantaran" style="max-width: 100%; max-height: 200px; border-radius: 8px; border: 1px solid var(--border);">
            </div>
            @endif
        </div>
        @elseif($order->status === 'completed')
        <div style="text-align: center; padding: 1rem; background: #D1FAE5; color: #065F46; border-radius: var(--radius-sm);">
            🎉 Pengantaran Selesai!
        </div>
        @endif
    </div>

    <!-- Rincian Pesanan (Opsional untuk diver) -->
    <div class="card" style="padding: 1.5rem; border-radius: var(--radius); margin-top: 1rem;">
        <h3 style="font-size: 1rem; margin-bottom: 1rem;">Rincian Item</h3>
        <ul style="list-style: none; padding: 0; margin: 0;">
            @foreach($order->orderItems as $item)
            <li style="display: flex; justify-content: space-between; padding-bottom: 0.5rem; margin-bottom: 0.5rem; border-bottom: 1px solid #E5E7EB;">
                <span>{{ $item->quantity }}x {{ $item->product->name }}</span>
            </li>
            @endforeach
        </ul>
    </div>
</div>

@if(!in_array($order->status, ['completed', 'canceled']))
@push('scripts')
<script>
    SmartRefresh.init({ pollUrl: '/driver/poll', interval: 10 });
</script>
@endpush
@endif
@endsection
