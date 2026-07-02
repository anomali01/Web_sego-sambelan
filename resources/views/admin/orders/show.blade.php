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
            <div class="alert alert-warning" style="margin:0 0 .75rem;">
                ⏳ Menunggu verifikasi pembayaran manual.
            </div>
            @else
            <p class="alert alert-success" style="margin:0 0 .75rem;">✓ Pembayaran sudah dikonfirmasi.</p>
            @endif
        </div>
        @endif

        <h3 style="margin-top:1.5rem; border-top:1px dashed var(--border); padding-top:1rem;">Verifikasi & Tindakan Pesanan</h3>
        <div class="order-action-panel" style="margin-top:1rem; display:flex; gap:.75rem; flex-wrap:wrap; align-items:center;">
            @if($order->status === 'pending')
                @if($order->payment?->isManual())
                    @if($order->payment->isPending())
                        <form action="/admin/orders/{{ $order->id }}/confirm-payment" method="POST" class="inline" onsubmit="return confirm('Konfirmasi bahwa pembayaran sudah masuk ke rekening?')">
                            @csrf
                            <button type="submit" class="btn btn-primary">① Verifikasi Pembayaran Diterima</button>
                        </form>
                    @else
                        <form action="/admin/orders/{{ $order->id }}/start-processing" method="POST" class="inline" onsubmit="return confirm('Mulai menyiapkan pesanan ini?')">
                            @csrf
                            <button type="submit" class="btn btn-success">② Mulai Menyiapkan Pesanan 👨‍🍳</button>
                        </form>
                    @endif
                @else
                    @if($order->payment?->isPaid())
                        <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="processed">
                            <button type="submit" class="btn btn-success">👨‍🍳 Mulai Menyiapkan Pesanan</button>
                        </form>
                    @else
                        <div class="alert alert-warning" style="margin:0; width:100%;">
                            ⏳ Menunggu pembayaran otomatis via Midtrans lunas...
                        </div>
                    @endif
                @endif

                {{-- Batalkan Button --}}
                <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="canceled">
                    <button type="submit" class="btn btn-danger">❌ Batalkan Pesanan</button>
                </form>

            @elseif($order->status === 'processed')
                @if($order->isDelivery())
                    @if($order->driver_id)
                        <div style="background: #EBF5FF; padding: 0.75rem; border-radius: var(--radius-sm); border-left: 4px solid #3B82F6; margin-bottom: 0.5rem; width: 100%;">
                            <p style="margin: 0; color: #1E40AF;"><strong>Driver Ditugaskan:</strong> {{ $order->driver->name }}</p>
                        </div>
                    @else
                        <form action="/admin/orders/{{ $order->id }}/assign-driver" method="POST" class="inline" style="background: var(--bg-color); padding: 0.75rem; border-radius: var(--radius-sm); width: 100%; display: flex; gap: 0.5rem; align-items: center; border: 1px solid var(--border);">
                            @csrf
                            <label for="driver_id" style="font-weight: 600; white-space: nowrap;">Tugaskan Driver:</label>
                            <select name="driver_id" id="driver_id" class="form-control" required style="flex-grow: 1; min-width: 150px;">
                                <option value="" disabled selected>Pilih Driver</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary" style="white-space: nowrap;">🛵 Tugaskan</button>
                        </form>
                    @endif
                @else
                    <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="inline" onsubmit="return confirm('Sajikan pesanan dan selesaikan transaksi?')">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn btn-success">🍽️ Sajikan & Selesai</button>
                    </form>
                @endif

                <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="canceled">
                    <button type="submit" class="btn btn-danger">❌ Batalkan Pesanan</button>
                </form>

            @elseif($order->status === 'delivering')
                <div class="alert alert-info" style="margin:0; width:100%;">
                    🛵 Pesanan sedang dalam perjalanan diantar oleh <strong>{{ $order->driver->name ?? 'Driver' }}</strong>.
                </div>
            @elseif($order->status === 'delivered')
                <div style="width: 100%; margin-bottom: 1rem;">
                    @if($order->delivery_proof_url)
                    <div style="margin-bottom: 1rem; padding: 1rem; background: var(--bg-color); border-radius: var(--radius-sm); border: 1px solid var(--border);">
                        <p style="font-weight: 600; margin-bottom: 0.5rem;">📸 Bukti Pengantaran Driver</p>
                        <a href="{{ $order->delivery_proof_url }}" target="_blank" rel="noopener">
                            <img src="{{ $order->delivery_proof_url }}" alt="Bukti" style="max-width: 250px; border-radius: 8px; border: 1px solid var(--border);">
                        </a>
                    </div>
                    @else
                    <div class="alert alert-warning" style="margin-bottom: 1rem;">
                        Driver menandai sudah sampai, tapi belum ada foto bukti.
                    </div>
                    @endif

                    <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="inline" onsubmit="return confirm('Verifikasi bukti pengantaran dan selesaikan pesanan?')">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn btn-success">✅ Verifikasi & Selesai</button>
                    </form>

                    <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="canceled">
                        <button type="submit" class="btn btn-danger">❌ Batalkan Pesanan</button>
                    </form>
                </div>

            @elseif($order->status === 'completed')
                <div class="alert alert-success" style="margin:0; width:100%; display:flex; align-items:center; gap:0.5rem;">
                    <span>✅</span> Pesanan ini sudah selesai sepenuhnya dan terarsip dalam riwayat pesanan sukses.
                </div>
            @elseif($order->status === 'canceled')
                <div class="alert alert-error" style="margin:0; width:100%; display:flex; align-items:center; gap:0.5rem;">
                    <span>❌</span> Pesanan ini telah dibatalkan dan terarsip dalam riwayat pembatalan.
                </div>
            @endif
        </div>
    </div>

    <div class="card glass-card">
        <h2 class="card-title">Info Pelanggan</h2>
        <div class="detail-list">
            <div class="detail-row"><span>Nama</span><span>{{ $order->user->name }}</span></div>
            <div class="detail-row"><span>Email</span><span>{{ $order->user->email }}</span></div>
            <div class="detail-row"><span>Telepon</span><span>{{ $order->user->profile?->phone ?? '-' }}</span></div>
            @if($order->delivery_address)
            <div class="detail-row" style="align-items: flex-start;">
                <span>Alamat</span>
                <div>
                    <span style="display: block; line-height: 1.4; color: var(--text-primary);">{{ $order->delivery_address }}</span>
                    <div style="margin-top: 0.5rem;">
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($order->delivery_address) }}" 
                           target="_blank" 
                           rel="noopener" 
                           class="btn btn-sm btn-outline" 
                           style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.5rem; font-size: 0.8rem; border-color: rgba(232,184,75,0.4); color: var(--primary);">
                            🗺️ Buka di Google Maps
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        @php
            $rawPhone = $order->user->profile?->phone ?? '';
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            if (strpos($cleanPhone, '0') === 0) {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            }
        @endphp
        @if($cleanPhone)
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border); text-align: right;">
            <a href="https://wa.me/{{ $cleanPhone }}?text=Halo%20{{ urlencode($order->user->name) }}%2C%20saya%20Admin%20dari%20Sego%20Sambelan.%20Menghubungi%20terkait%20pesanan%20Anda%20*{{ urlencode($order->order_number) }}*." target="_blank" class="btn btn-sm" style="background: #25D366; color: white; display: inline-flex; align-items: center; gap: 0.5rem; border: none; font-weight: bold; box-shadow: 0 4px 15px rgba(37,211,102,0.3);">
                💬 Hubungi Pembeli (WA)
            </a>
        </div>
        @endif
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

@if(!in_array($order->status, ['completed', 'canceled']))
@push('scripts')
<script>
    SmartRefresh.init({ pollUrl: '/admin/poll', interval: 10 });
</script>
@endpush
@endif
@endsection
