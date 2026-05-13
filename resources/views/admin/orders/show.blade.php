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
            <div class="detail-row"><span>Pembayaran</span><span class="badge {{ $order->payment?->status_badge_class ?? 'badge-secondary' }}">{{ ucfirst($order->payment?->payment_status ?? 'N/A') }}</span></div>
        </div>

        <h3 style="margin-top:1rem;">Ubah Status</h3>
        <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="form-row">
            @csrf @method('PATCH')
            <select name="status" class="form-input">
                @foreach(['pending','processed','delivered','completed','canceled'] as $s)
                <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
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
