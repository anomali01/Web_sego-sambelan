@extends('layouts.admin')
@section('title', 'Detail Pesanan')
@section('page-title', 'Detail Pesanan')

@section('content')
<div class="order-detail-grid">
    <div class="card glass-card">
        <h2 class="card-title">Info Pesanan</h2>
        <div class="detail-list">
            <div class="detail-row"><span>No. Order</span><strong>{{ $order->order_number }}</strong></div>
            <div class="detail-row"><span>Waktu</span><span>{{ $order->created_at->format('d M Y, H:i') }}</span></div>
            <div class="detail-row"><span>Tipe</span>
                <span class="badge {{ $order->isDelivery() ? 'badge-info' : 'badge-warning' }}">
                    {{ $order->isDelivery() ? '🚚 Delivery' : '🍽️ Dine-In (Meja ' . $order->table_number . ')' }}
                </span>
            </div>
            <div class="detail-row"><span>Status</span><span class="badge {{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span></div>
            <div class="detail-row"><span>Metode</span><span>{{ $order->payment?->isManual() ? '🏦 Transfer Manual' : '💳 Midtrans' }}</span></div>
            <div class="detail-row"><span>Pembayaran</span><span class="badge {{ $order->payment?->status_badge_class ?? 'badge-secondary' }}">{{ ucfirst($order->payment?->payment_status ?? 'N/A') }}</span></div>
        </div>

        @if($order->payment?->isManual())
        @php
            $manualUnpaid = $order->payment->isPending();
            $canStartCooking = $order->payment->isPaid() && $order->status === 'pending';
        @endphp
        <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid var(--border);">
            <p style="font-weight:600; margin-bottom:.75rem;">Alur transfer manual (2 langkah)</p>
            <ol style="margin:0 0 1rem 1.25rem; color:var(--text-muted); font-size:.9rem;">
                <li>Verifikasi pembayaran → status bayar <strong>paid</strong></li>
                <li>Mulai menyiapkan → status pesanan <strong>processed</strong></li>
            </ol>
            @if($order->payment->sender_name)
            <div class="detail-row"><span>Nama pengirim</span><span>{{ $order->payment->sender_name }}</span></div>
            @endif
            @if($order->payment->hasProof())
            <div style="margin:.75rem 0;">
                <p style="font-weight:600; margin-bottom:.5rem;">Bukti transfer</p>
                <a href="{{ $order->payment->proof_url }}" target="_blank" rel="noopener">
                    <img src="{{ $order->payment->proof_url }}" alt="Bukti" style="max-width:280px; border-radius:8px; border:1px solid var(--border);">
                </a>
            </div>
            @else
            <p class="alert alert-warning" style="margin:0 0 1rem;">Belum ada bukti transfer dari pembeli.</p>
            @endif

            @if($manualUnpaid)
            <form action="/admin/orders/{{ $order->id }}/confirm-payment" method="POST" style="margin-bottom:.75rem;" onsubmit="return confirm('Konfirmasi bahwa pembayaran sudah masuk ke rekening?')">
                @csrf
                <button type="submit" class="btn btn-primary">① Konfirmasi Pembayaran Diterima</button>
            </form>
            @else
            <p class="alert alert-success" style="margin:0 0 .75rem;">✓ Pembayaran sudah dikonfirmasi.</p>
            @endif

            @if($canStartCooking)
            <form action="/admin/orders/{{ $order->id }}/start-processing" method="POST" onsubmit="return confirm('Mulai menyiapkan pesanan ini?')">
                @csrf
                <button type="submit" class="btn btn-primary">② Mulai Menyiapkan Pesanan 👨‍🍳</button>
            </form>
            @endif
        </div>
        @endif

        <h3 style="margin-top:1rem;">Ubah Status</h3>
        @if($order->payment?->isManual() && $order->payment->isPending())
        <p class="alert alert-warning" style="font-size:.9rem;">Untuk transfer manual: konfirmasi pembayaran dulu sebelum ubah status ke Proses/Delivery/Selesai.</p>
        @endif
        <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="form-row">
            @csrf @method('PATCH')
            <select name="status" class="form-input">
                @foreach(['pending','processed','delivered','completed','canceled'] as $s)
                @php
                    $blocked = $order->payment?->isManual()
                        && $order->payment->isPending()
                        && in_array($s, ['processed', 'delivered', 'completed']);
                @endphp
                <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }} {{ $blocked ? 'disabled' : '' }}>
                    {{ ucfirst($s) }}{{ $blocked ? ' (konfirmasi bayar dulu)' : '' }}
                </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>

    <div class="card glass-card">
        <h2 class="card-title">Info Pelanggan</h2>
        <div class="detail-list">
            <div class="detail-row"><span>Nama</span><span>{{ $order->user->name }}</span></div>
            <div class="detail-row"><span>Email</span><span>{{ $order->user->email }}</span></div>
            <div class="detail-row"><span>Telepon</span><span>{{ $order->user->profile?->phone ?? '-' }}</span></div>
            @if($order->delivery_address)
            <div class="detail-row"><span>Alamat</span><span>{{ $order->delivery_address }}</span></div>
            @endif
        </div>
    </div>
</div>

<div class="card glass-card">
    <h2 class="card-title">Item Pesanan</h2>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr><th>Menu</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'Menu dihapus' }}</td>
                    <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->formatted_subtotal }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><td colspan="3" class="text-right"><strong>Total</strong></td><td><strong>{{ $order->formatted_total }}</strong></td></tr>
            </tfoot>
        </table>
    </div>
    @if($order->notes)
    <div class="order-notes"><strong>Catatan:</strong> {{ $order->notes }}</div>
    @endif
</div>

<a href="/admin/orders" class="btn btn-outline">← Kembali ke Daftar</a>
@endsection
